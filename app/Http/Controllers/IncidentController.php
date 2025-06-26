<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class IncidentController extends Controller
{
    /**
     * Display a listing of incidents.
     */
    public function index(Request $request)
    {
        $query = Incident::query()
            ->with('user:id,name') // Eager load user relationship
            ->latest();

        // Apply filters
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('area', 'like', '%'.$request->search.'%')
                  ->orWhere('details', 'like', '%'.$request->search.'%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Date range filter
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('created_at', [
                $request->date_from,
                $request->date_to
            ]);
        }

        $incidents = $query->paginate(15)
            ->withQueryString();

            dd($incidents->toArray());

        return Inertia::render('Incidents/Index', [
            'incidents' => $incidents,
            'filters' => $request->only(['search', 'status', 'type', 'date_from', 'date_to']),
            'statusOptions' => ['reported', 'investigating', 'resolved', 'closed'],
            'typeOptions' => ['accident', 'crime', 'natural_disaster', 'health_emergency', 'other'],
            'stats' => $this->stats(),
        ]);
    }

    /**
     * Show the form for creating a new incident.
     */
    public function create()
    {
        return Inertia::render('Incidents/Create', [
            'typeOptions' => ['accident', 'crime', 'natural_disaster', 'health_emergency', 'other'],
        ]);
    }

    /**
     * Store a newly created incident.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:accident,crime,natural_disaster,health_emergency,other',
            'area' => 'required|string|max:255',
            'details' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'status' => 'sometimes|in:reported,investigating,resolved,closed',
        ]);

        // Set current user as reporter
        $validated['user_id'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'reported';

        $incident = Incident::create($validated);

        return redirect()->route('incidents.index')
            ->with('success', 'Incident reported successfully');
    }

    /**
     * Display the specified incident.
     */
    public function show(Incident $incident)
    {
        return Inertia::render('Incidents/Show', [
            'incident' => $incident->load('user:id,name,email'),
        ]);
    }

    /**
     * Show the form for editing the specified incident.
     */
    public function edit(Incident $incident)
    {
        return Inertia::render('Incidents/Edit', [
            'incident' => $incident,
            'typeOptions' => ['accident', 'crime', 'natural_disaster', 'health_emergency', 'other'],
            'statusOptions' => ['reported', 'investigating', 'resolved', 'closed'],
        ]);
    }

    /**
     * Update the specified incident.
     */
    public function update(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:accident,crime,natural_disaster,health_emergency,other',
            'area' => 'required|string|max:255',
            'details' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'status' => 'required|in:reported,investigating,resolved,closed',
        ]);

        $incident->update($validated);

        return redirect()->route('incidents.index')
            ->with('success', 'Incident updated successfully');
    }

    /**
     * Remove the specified incident.
     */
    public function destroy(Incident $incident)
    {
        $incident->delete();

        return redirect()->route('incidents.index')
            ->with('success', 'Incident deleted successfully');
    }

    /**
     * Update incident status
     */
    public function updateStatus(Incident $incident, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:reported,investigating,resolved,closed'
        ]);

        $incident->update($validated);

        return back()->with('success', 'Incident status updated');
    }

    /**
     * Get incident statistics for dashboard
     */
    public function stats()
    {
        return response()->json([
            'total' => Incident::count(),
            'reported' => Incident::where('status', 'reported')->count(),
            'investigating' => Incident::where('status', 'investigating')->count(),
            'resolved' => Incident::where('status', 'resolved')->count(),
            'today' => Incident::whereDate('created_at', today())->count(),
            'week' => Incident::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'by_type' => Incident::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get()
        ]);
    }
}
