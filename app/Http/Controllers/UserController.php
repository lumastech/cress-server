<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use app\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // if user role is not admin, redirect to dashboard
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view activity logs.');
        }
        // Fetch users from the database
        $users = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        // Pass the users to the Inertia view
        return Inertia::render('Users/index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    public function updateStatus(Request $request, string $id) {
        // if user role is not admin, redirect to dashboard
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view activity logs.');
        }
        // Validate the request
        $request->validate([
            'status' => ['required', 'string', 'in:active,inactive,pending,suspended,deleted,rejected'],
        ]);

        $user = User::findOrFail($id);
        $user->status = $request->input('status');
        $user->save();

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    public function updateRole(Request $request, string $id) {
        // if user role is not admin, redirect to dashboard
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view activity logs.');
        }
        // Validate the request
        $request->validate([
            'role' => ['required', 'string', 'in:admin,user,moderator'],
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->input('role');
        $user->save();

        return redirect()->back()->with('success', 'User role updated successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // search users
    public function search(Request $request) {
        // if user role is not admin, redirect to dashboard
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view activity logs.');
        }
        // Fetch users from the database
        $users = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        return $users;
        // Pass the users to the Inertia view
        // return Inertia::render('Users/Index', [
        //     'users' => $users,
        //     'filters' => $request->only(['search']),
        // ]);
    }
}
