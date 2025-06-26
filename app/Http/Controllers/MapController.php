<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class MapController extends Controller
{
    /**
     * Display the danger zones visualization page
     */
    public function index()
    {
        // Get some initial data to pre-load the page
        $initialAlerts = Alert::query()
            ->whereNotNull(['lat', 'lng'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get(['lat', 'lng', 'created_at']);

        $initialIncidents = Incident::query()
            ->whereNotNull(['lat', 'lng'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get(['lat', 'lng', 'created_at', 'status']);

        // Calculate default center (average of all points or fallback to a known location)
        $defaultCenter = $this->calculateDefaultCenter($initialAlerts, $initialIncidents);

        return Inertia::render('Zones/index', [
            'initialAlerts' => $initialAlerts,
            'initialIncidents' => $initialIncidents,
            'defaultCenter' => $defaultCenter,
        ]);
    }

    /**
     * Get heatmap data for danger zones visualization
     */
    public function dangerZonesHeatmap(Request $request)
    {
        $validated = $request->validate([
            'time_range' => 'sometimes|in:24h,7d,30d,all',
            'layers' => 'sometimes|array',
            'layers.alerts' => 'sometimes|boolean',
            'layers.incidents' => 'sometimes|boolean',
            'bounds' => 'sometimes|array',
            'bounds.north' => 'required_with:bounds|numeric',
            'bounds.south' => 'required_with:bounds|numeric',
            'bounds.east' => 'required_with:bounds|numeric',
            'bounds.west' => 'required_with:bounds|numeric',
            'zoom' => 'sometimes|numeric|min:1|max:20',
        ]);

        // Default values
        $timeRange = $validated['time_range'] ?? '7d';
        $layers = $validated['layers'] ?? ['alerts' => true, 'incidents' => false];
        $shouldFilterBounds = isset($validated['bounds']);

        // Get cutoff date
        $cutoffDate = $this->getCutoffDate($timeRange);

        // Prepare base queries
        $alertQuery = Alert::query()
            ->where('created_at', '>=', $cutoffDate)
            ->whereNotNull(['lat', 'lng']);

        $incidentQuery = Incident::query()
            ->where('created_at', '>=', $cutoffDate)
            ->whereNotNull(['lat', 'lng']);

        // Apply spatial filtering if bounds provided
        if ($shouldFilterBounds) {
            $bounds = $validated['bounds'];
            $alertQuery->whereBetween('lat', [$bounds['south'], $bounds['north']])
                      ->whereBetween('lng', [$bounds['west'], $bounds['east']]);

            $incidentQuery->whereBetween('lat', [$bounds['south'], $bounds['north']])
                         ->whereBetween('lng', [$bounds['west'], $bounds['east']]);
        }

        // Process data based on selected layers
        $heatmapData = [];
        $stats = [
            'total_points' => 0,
            'alert_count' => 0,
            'incident_count' => 0,
            'density_level' => 'low' // low, medium, high
        ];

        // Process alerts if layer is enabled
        if ($layers['alerts'] ?? false) {
            $alerts = $alertQuery->selectRaw('
                ROUND(lat, 4) as lat_rounded,
                ROUND(lng, 4) as lng_rounded,
                COUNT(*) as count
            ')
            ->groupBy('lat_rounded', 'lng_rounded')
            ->get();

            $stats['alert_count'] = $alerts->sum('count');

            foreach ($alerts as $alert) {
                $heatmapData[] = [
                    'lat' => (float)$alert->lat_rounded,
                    'lng' => (float)$alert->lng_rounded,
                    'weight' => $this->calculateWeight($alert->count, 'alert'),
                    'type' => 'alert'
                ];
            }
        }

        // Process incidents if layer is enabled
        if ($layers['incidents'] ?? false) {
            $incidents = $incidentQuery->selectRaw('
                ROUND(lat, 4) as lat_rounded,
                ROUND(lng, 4) as lng_rounded,
                COUNT(*) as count,
                AVG(CASE WHEN severity IS NOT NULL THEN severity ELSE 3 END) as avg_severity
            ')
            ->groupBy('lat_rounded', 'lng_rounded')
            ->get();

            $stats['incident_count'] = $incidents->sum('count');

            foreach ($incidents as $incident) {
                $heatmapData[] = [
                    'lat' => (float)$incident->lat_rounded,
                    'lng' => (float)$incident->lng_rounded,
                    'weight' => $this->calculateWeight($incident->count, 'incident', $incident->avg_severity),
                    'type' => 'incident'
                ];
            }
        }

        // Calculate density level
        $stats['total_points'] = count($heatmapData);
        $stats['density_level'] = $this->calculateDensityLevel($stats['total_points'], $validated['zoom'] ?? 12);

        return response()->json([
            'data' => $heatmapData,
            'stats' => $stats,
            'meta' => [
                'time_range' => $timeRange,
                'cutoff_date' => $cutoffDate,
                'layers' => $layers,
                'bounds_applied' => $shouldFilterBounds
            ]
        ]);
    }

    /**
     * Get clustered points for map markers
     */
    public function dangerZonesClusters(Request $request)
    {
        $validated = $request->validate([
            'time_range' => 'sometimes|in:24h,7d,30d,all',
            'bounds' => 'required|array',
            'bounds.north' => 'required|numeric',
            'bounds.south' => 'required|numeric',
            'bounds.east' => 'required|numeric',
            'bounds.west' => 'required|numeric',
            'zoom' => 'required|numeric|min:1|max:20',
        ]);

        $cutoffDate = $this->getCutoffDate($validated['time_range'] ?? '7d');
        $bounds = $validated['bounds'];
        $zoom = $validated['zoom'];

        // The grid size depends on zoom level (smaller grid at higher zoom)
        $gridSize = $this->calculateGridSize($zoom);

        // Get aggregated alerts
        $alerts = Alert::query()
            ->selectRaw(
                "FLOOR(lat * {$gridSize}) / {$gridSize} as grid_lat,
                FLOOR(lng * {$gridSize}) / {$gridSize} as grid_lng,
                COUNT(*) as count"
            )
            ->where('created_at', '>=', $cutoffDate)
            ->whereBetween('lat', [$bounds['south'], $bounds['north']])
            ->whereBetween('lng', [$bounds['west'], $bounds['east']])
            ->groupBy('grid_lat', 'grid_lng')
            ->get()
            ->map(function ($item) {
                return [
                    'lat' => (float)$item->grid_lat,
                    'lng' => (float)$item->grid_lng,
                    'count' => $item->count,
                    'type' => 'alert'
                ];
            });

        // Get aggregated incidents
        $incidents = Incident::query()
            ->selectRaw(
                "FLOOR(lat * {$gridSize}) / {$gridSize} as grid_lat,
                FLOOR(lng * {$gridSize}) / {$gridSize} as grid_lng,
                COUNT(*) as count,
                MAX(severity) as max_severity"
            )
            ->where('created_at', '>=', $cutoffDate)
            ->whereBetween('lat', [$bounds['south'], $bounds['north']])
            ->whereBetween('lng', [$bounds['west'], $bounds['east']])
            ->groupBy('grid_lat', 'grid_lng')
            ->get()
            ->map(function ($item) {
                return [
                    'lat' => (float)$item->grid_lat,
                    'lng' => (float)$item->grid_lng,
                    'count' => $item->count,
                    'severity' => $item->max_severity,
                    'type' => 'incident'
                ];
            });

        return response()->json([
            'clusters' => [
                'alerts' => $alerts,
                'incidents' => $incidents
            ],
            'grid_size' => $gridSize,
            'zoom' => $zoom
        ]);
    }

    /**
     * Get danger zone statistics for dashboard
     */
    public function dangerZonesStats()
    {
        $cutoff24h = $this->getCutoffDate('24h');
        $cutoff7d = $this->getCutoffDate('7d');

        return response()->json([
            'alerts' => [
                'last_24h' => Alert::where('created_at', '>=', $cutoff24h)->count(),
                'last_7d' => Alert::where('created_at', '>=', $cutoff7d)->count(),
                'total' => Alert::count(),
            ],
            'incidents' => [
                'last_24h' => Incident::where('created_at', '>=', $cutoff24h)->count(),
                'last_7d' => Incident::where('created_at', '>=', $cutoff7d)->count(),
                'total' => Incident::count(),
            ],
            'hotspots' => $this->getTopHotspots(5),
            'updated_at' => now()->toDateTimeString()
        ]);
    }

    protected function calculateDefaultCenter($alerts, $incidents)
    {
        // Try to calculate center from existing data
        if ($alerts->count() > 0 || $incidents->count() > 0) {
            $points = collect($alerts)->merge($incidents);

            $avgLat = $points->avg('lat');
            $avgLng = $points->avg('lng');

            if ($avgLat && $avgLng) {
                return ['lat' => $avgLat, 'lng' => $avgLng];
            }
        }

        // Fallback to a reasonable default (e.g., center of Lusaka)
        return ['lat' => -15.3875, 'lng' => 28.3228];
    }

    protected function getCutoffDate(string $range): Carbon
    {
        return match ($range) {
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => Carbon::createFromTimestamp(0),
        };
    }

    protected function calculateWeight(int $count, string $type, float $severity = null): float
    {
        // Base weight + additional factors
        $baseWeight = $type === 'alert' ? 1 : 2;
        $severityFactor = $severity ? ($severity / 5) : 1; // Normalize severity 1-5

        return $baseWeight * $count * $severityFactor;
    }

    protected function calculateDensityLevel(int $pointCount, int $zoom): string
    {
        // Adjust thresholds based on zoom level
        $threshold = match (true) {
            $zoom >= 15 => 10,  // High zoom - smaller area
            $zoom >= 10 => 30,  // Medium zoom
            default => 50       // Low zoom - large area
        };

        if ($pointCount > $threshold * 3) return 'high';
        if ($pointCount > $threshold) return 'medium';
        return 'low';
    }

    protected function calculateGridSize(int $zoom): float
    {
        // Larger grid at lower zoom levels (more aggregation)
        return match (true) {
            $zoom >= 15 => 100,  // ~11 meter precision
            $zoom >= 10 => 50,   // ~22 meter precision
            default => 20        // ~55 meter precision
        };
    }

    protected function getTopHotspots(int $limit): array
    {
        // Get areas with highest concentration of alerts + incidents
        return Incident::query()
            ->selectRaw('
                ROUND(lat, 3) as lat,
                ROUND(lng, 3) as lng,
                COUNT(*) as incident_count,
                (SELECT COUNT(*) FROM alerts
                 WHERE ROUND(alerts.lat, 3) = ROUND(incidents.lat, 3)
                 AND ROUND(alerts.lng, 3) = ROUND(incidents.lng, 3)
                ) as alert_count
            ')
            ->groupBy('lat', 'lng')
            ->orderByRaw('(incident_count * 2) + alert_count DESC') // Weight incidents heavier
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'coordinates' => [(float)$item->lat, (float)$item->lng],
                    'score' => ($item->incident_count * 2) + $item->alert_count,
                    'incidents' => $item->incident_count,
                    'alerts' => $item->alert_count
                ];
            })
            ->toArray();
    }
}
