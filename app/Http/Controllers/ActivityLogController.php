<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Inertia\Inertia;

class ActivityLogController extends Controller
{

    public $perPage = 10;
    public $search = '';
    public $eventFilter = '';
    public $logNameFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'eventFilter' => ['except' => ''],
        'logNameFilter' => ['except' => ''],
    ];
    public function index() {
        // if user role is not admin, redirect to dashboard
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view activity logs.');
        }
    $logs = ActivityLog::with(['subject', 'causer'])
            ->when($this->search, function ($query) {
                $query->where('description', 'like', '%'.$this->search.'%');
            })
            ->when($this->eventFilter, function ($query) {
                $query->where('event', $this->eventFilter);
            })
            ->when($this->logNameFilter, function ($query) {
                $query->where('log_name', $this->logNameFilter);
            })
            ->latest()
            ->paginate($this->perPage);

        return Inertia::render('ActivityLogs/Index', [
            'logs' => $logs,
            'eventTypes' => ActivityLog::distinct()->pluck('event'),
            'logNames' => ActivityLog::distinct()->pluck('log_name'),
            'filters' => request()->all('search', 'perPage'),
        ]);
    }

    // function () {
    //     return Inertia::render('ActivityLogs/Index', [
    //         'logs' => \App\Models\ActivityLog::latest()->paginate(10)->withQueryString(),
    //         'filters' => request()->all('search', 'perPage'),
    //     ]);
    // }


}
