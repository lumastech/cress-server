<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Alert;
use App\Models\Incident;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index() {
        // Basic counts
        $stats = [
            'totalUsers' => User::count(),
            'activeAlerts' => Alert::where('status', 'active')
            ->orWhere('status', 'pending')
            ->orWhere('status', 'sent')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'incidentsToday' => Incident::where('created_at', '>=', now()->subDay())
                ->count(),
            'systemUptime' => 99.9, // Cache::remember('system_uptime', now()->addHour(), function () {
        //         return 99.8; // $this->calculateSystemUptime(); // Your uptime calculation logic
        // }),
        ];

        // Alert trends data (last 7 days)
        $alertTrends = Alert::selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->where('created_at', '>=', now()->subDays(7))
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->map(function ($item) {
            return [
                'date' => $item->date,
                'count' => $item->count,
                'formatted_date' => \Carbon\Carbon::parse($item->date)->format('M d'),
            ];
        });

        // User growth data (last 30 days)
        $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent activities
        $recentActivities = ActivityLog::with(['causer', 'subject'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'description' => $activity->description,
                    'causer' => $activity->causer?->name ?? 'System',
                    'subject' => $activity->subject?->name ?? class_basename($activity->subject_type),
                    'time' => $activity->created_at->diffForHumans(),
                    'icon' => 'info' //$this->getActivityIcon($activity->event),
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'alertTrends' => $alertTrends,
            'userGrowth' => $userGrowth,
            'recentActivities' => $recentActivities,
        ]);
    }
}
