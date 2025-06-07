<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AlertController extends Controller
{
    /**
     * Display a listing of alerts.
     */
    public function index(Request $request)
    {
        $query = Alert::query();

        // Search filter
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Status filter
        if ($request->has('status')) {
            if ($request->status != ''){
                $query->where('status', $request->status);
            }
        }

        // Date range filter
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('initiated_at', [
                $request->date_from,
                $request->date_to
            ]);
        }

        $alerts = $query->latest()->paginate(15);

        return Inertia::render("Alerts/index", ["alerts"=>$alerts, "filter"=>$request->search]);
    }

    /**
     * Store a newly created alert.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'status' => 'sometimes|in:pending,active,resolved',
            'message' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'accuracy' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $alert = Alert::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->status ?? 'pending',
            'message' => $request->message,
            'initiated_at' => now(),
            'lat' => $request->lat,
            'lng' => $request->lng,
            'accuracy' => $request->accuracy
        ]);

        return response()->json([
            'message' => 'Alert created successfully',
            'alert' => $alert
        ], 201);
    }

    /**
     * Display the specified alert.
     */
    public function show(Alert $alert)
    {
        return response()->json([
            'alert' => $alert->load('user')
        ]);
    }

    /**
     * Update the specified alert.
     */
    public function update(Request $request, Alert $alert)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,active,resolved',
            'message' => 'sometimes|string',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric',
            'accuracy' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $alert->update($request->only([
            'status', 'message', 'lat', 'lng', 'accuracy'
        ]));

        return response()->json([
            'message' => 'Alert updated successfully',
            'alert' => $alert
        ]);
    }

    /**
     * Remove the specified alert.
     */
    public function destroy(Alert $alert)
    {
        $alert->delete();

        return response()->json([
            'message' => 'Alert deleted successfully'
        ]);
    }

    /**
     * Get alert statistics
     */
    public function stats()
    {
        $stats = [
            'total' => Alert::count(),
            'active' => Alert::where('status', 'active')->count(),
            'resolved' => Alert::where('status', 'resolved')->count(),
            'pending' => Alert::where('status', 'pending')->count(),
            'today' => Alert::whereDate('initiated_at', today())->count(),
            'week' => Alert::whereBetween('initiated_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
        ];

        return response()->json($stats);
    }
}
