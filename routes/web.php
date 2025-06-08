<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\CenterController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\APKController;

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/privacy-policy', function () {
    return Inertia::render('TermsOfService', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/download-apk', [APKController::class, 'download'])->name('apk.download');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard',[DashboardController::class, "index"])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::get('users/{filter}', [UserController::class, 'index']);

    Route::resource('alerts', AlertController::class);
    Route::get('alerts/stats', [AlertController::class, 'stats']);

    Route::resource('health-centers', CenterController::class);
    Route::post('health-centers/{health_center}/toggle-verification', [CenterController::class, 'toggleVerification'])->name('health-centers.toggle-verification');
    Route::get('health-centers/stats', [CenterController::class, 'stats'])->name('health-centers.stats');

    Route::resource('incidents', IncidentController::class);

    Route::post('incidents/{incident}/update-status', [IncidentController::class, 'updateStatus'])->name('incidents.update-status');

    Route::get('incidents/stats', [IncidentController::class, 'stats'])->name('incidents.stats');

    Route::get('/danger-zones', [MapController::class, 'index']);
    Route::get('/api/danger-zones/heatmap', [MapController::class, 'dangerZonesHeatmap']);
    Route::get('/api/danger-zones/clusters', [MapController::class, 'dangerZonesClusters']);
    Route::get('/api/danger-zones/stats', [MapController::class, 'dangerZonesStats']);

    Route::post('/upload-apk', [APKController::class, 'upload'])->name('apk.upload');


});
