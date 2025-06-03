<?php

namespace App\Http\Controllers;

use App\Models\Center;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class CenterController extends Controller
{
    /**
     * Display a listing of health centers.
     */
    public function index(Request $request)
    {
        $query = Center::query()
            ->with('user:id,name') // Eager load user relationship
            ->latest();

        // Apply filters
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('verified')) {
            $query->where('is_verified', $request->verified === 'true');
        }

        $centers = $query->paginate(15)
            ->withQueryString();

        return Inertia::render('HealthCenters/Index', [
            'centers' => $centers,
            'filters' => $request->only(['search', 'status', 'type', 'verified']),
            'statusOptions' => ['active', 'inactive', 'pending'],
            'typeOptions' => ['hospital', 'clinic', 'pharmacy', 'laboratory', 'other'],
        ]);
    }

    /**
     * Show the form for creating a new health center.
     */
    public function create()
    {
        return Inertia::render('HealthCenters/Create', [
            'typeOptions' => ['hospital', 'clinic', 'pharmacy', 'laboratory', 'other'],
        ]);
    }

    /**
     * Store a newly created health center.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:centers,email',
            'phone' => 'required|string|max:20',
            'type' => 'required|in:hospital,clinic,pharmacy,laboratory,other',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'status' => 'sometimes|in:active,inactive,pending',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'is_verified' => 'sometimes|boolean',
        ]);

        // Set current user as creator
        $validated['user_id'] = auth()->id();
        $validated['is_verified'] = $validated['is_verified'] ?? false;

        $center = Center::create($validated);

        return redirect()->route('health-centers.index')
            ->with('success', 'Health center created successfully');
    }

    /**
     * Display the specified health center.
     */
    public function show(Center $health_center)
    {
        return Inertia::render('HealthCenters/Show', [
            'center' => $health_center->load('user:id,name,email'),
        ]);
    }

    /**
     * Show the form for editing the specified health center.
     */
    public function edit(Center $health_center)
    {
        return Inertia::render('HealthCenters/Edit', [
            'center' => $health_center,
            'typeOptions' => ['hospital', 'clinic', 'pharmacy', 'laboratory', 'other'],
            'statusOptions' => ['active', 'inactive', 'pending'],
        ]);
    }

    /**
     * Update the specified health center.
     */
    public function update(Request $request, Center $health_center)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:centers,email,'.$health_center->id,
            'phone' => 'required|string|max:20',
            'type' => 'required|in:hospital,clinic,pharmacy,laboratory,other',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'status' => 'required|in:active,inactive,pending',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'is_verified' => 'required|boolean',
        ]);

        $health_center->update($validated);

        return redirect()->route('health-centers.index')
            ->with('success', 'Health center updated successfully');
    }

    /**
     * Remove the specified health center.
     */
    public function destroy(Center $health_center)
    {
        $health_center->delete();

        return redirect()->route('health-centers.index')
            ->with('success', 'Health center deleted successfully');
    }

    /**
     * Toggle verification status
     */
    public function toggleVerification(Center $health_center)
    {
        $health_center->update([
            'is_verified' => !$health_center->is_verified
        ]);

        return back()->with('success', 'Verification status updated');
    }

    /**
     * Get health center statistics for dashboard
     */
    public function stats()
    {
        return response()->json([
            'total' => Center::count(),
            'verified' => Center::where('is_verified', true)->count(),
            'hospitals' => Center::where('type', 'hospital')->count(),
            'clinics' => Center::where('type', 'clinic')->count(),
            'pharmacies' => Center::where('type', 'pharmacy')->count(),
        ]);
    }
}
