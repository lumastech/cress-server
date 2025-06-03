<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::get("/token/verify", [ApiController::class, "tokenVerify"])->middleware('auth:sanctum');
    Route::get("/token/delete", [ApiController::class, "tokenDelete"])->middleware('auth:sanctum');
    Route::post("/token/create", [ApiController::class, "token"]);
});

// User Routes
Route::prefix('users')->group(function () {
    Route::get('/', [ApiController::class, 'getUsers']);
    Route::get('/{id}', [ApiController::class, 'getUser']);
    Route::post('/', [ApiController::class, 'createUser']);
    Route::put('/{id}', [ApiController::class, 'updateUser']);
    Route::delete('/{id}', [ApiController::class, 'deleteUser']);
});

// Alert Routes
Route::middleware('auth:sanctum')->prefix('alerts')->group(function () {
    Route::get('/', [ApiController::class, 'getAlerts']);
    Route::get('/{id}', [ApiController::class, 'getAlert']);
    Route::post('/', [ApiController::class, 'createAlert']);
    Route::put('/{id}', [ApiController::class, 'updateAlert']);
    Route::delete('/{id}', [ApiController::class, 'deleteAlert']);
});

// Center Routes
Route::middleware('auth:sanctum')->prefix('centers')->group(function () {
    Route::get('/', [ApiController::class, 'getCenters']);
    Route::get('/{id}', [ApiController::class, 'getCenter']);
    Route::get('/filter/{filter}', [ApiController::class, 'searchCenter']);
    Route::post('/', [ApiController::class, 'createCenter']);
    Route::put('/{id}', [ApiController::class, 'updateCenter']);
    Route::delete('/{id}', [ApiController::class, 'deleteCenter']);
    Route::delete('/byname/{id}', [ApiController::class, 'deleteCenterByname']);
});

// Contact Routes
Route::middleware('auth:sanctum')->prefix('contacts')->group(function () {
    Route::get('/', [ApiController::class, 'getContacts']);
    Route::get('/{id}', [ApiController::class, 'getContact']);
    Route::post('/', [ApiController::class, 'createContact']);
    Route::put('/{id}', [ApiController::class, 'updateContact']);
    Route::delete('/{id}', [ApiController::class, 'deleteContact']);
});

// File Routes
Route::prefix('files')->group(function () {
    Route::get('/', [ApiController::class, 'getFiles']);
    Route::get('/{id}', [ApiController::class, 'getFile']);
    Route::post('/', [ApiController::class, 'createFile']);
    Route::put('/{id}', [ApiController::class, 'updateFile']);
    Route::delete('/{id}', [ApiController::class, 'deleteFile']);
});

// Image Routes
Route::prefix('images')->group(function () {
    Route::get('/', [ApiController::class, 'getImages']);
    Route::get('/{id}', [ApiController::class, 'getImage']);
    Route::post('/', [ApiController::class, 'createImage']);
    Route::put('/{id}', [ApiController::class, 'updateImage']);
    Route::delete('/{id}', [ApiController::class, 'deleteImage']);
});

// Incident Routes
Route::middleware('auth:sanctum')->prefix('incidents')->group(function () {
    Route::get('/', [ApiController::class, 'getIncidents']);
    Route::get('/{id}', [ApiController::class, 'getIncident']);
    Route::post('/', [ApiController::class, 'createIncident']);
    Route::put('/{id}', [ApiController::class, 'updateIncident']);
    Route::delete('/{id}', [ApiController::class, 'deleteIncident']);
});
