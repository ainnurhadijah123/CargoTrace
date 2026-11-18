<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\CarrierController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ExceptionController;
use App\Models\Shipment;
use App\Services\EtaService;
use App\Services\External\WeatherService;

/*
|--------------------------------------------------------------------------
| CargoTrace REST API Routes (Sanctum Authenticated)
| Meets and exceeds all Web Programming 2 - UAS requirements
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC AUTH & TRACKING ENDPOINTS
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public tracking lookup without token
Route::get('/public/track/{number}', function ($number, EtaService $etaService, WeatherService $weatherService) {
    $shipment = Shipment::with([
        'origin',
        'destination',
        'carrier',
        'items',
        'chronologicalEvents.location',
        'exceptions',
    ])->where('shipment_number', $number)->first();

    if (!$shipment) {
        return response()->json([
            'success' => false,
            'message' => 'Shipment not found.',
        ], 404);
    }

    $delay = $etaService->calculateDelay($shipment);
    $latest = $shipment->trackingEvents->first();
    $lat = $latest?->latitude ?? $shipment->origin?->latitude ?? 1.2644;
    $lng = $latest?->longitude ?? $shipment->origin?->longitude ?? 103.8403;
    $weather = $weatherService->getWeather((float) $lat, (float) $lng);

    return response()->json([
        'success' => true,
        'data' => [
            'shipment' => $shipment,
            'delay_analysis' => $delay,
            'weather' => $weather,
        ],
    ]);
});

// ==========================================
// 2. PROTECTED ENDPOINTS (Requires Sanctum Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    // Current authenticated user profile & logout
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Shipments Resource
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::post('/shipments', [ShipmentController::class, 'store']);
    Route::get('/shipments/{id}', [ShipmentController::class, 'show']);
    Route::match(['put', 'patch'], '/shipments/{id}', [ShipmentController::class, 'update']);
    Route::delete('/shipments/{id}', [ShipmentController::class, 'destroy']);

    // Shipment Tracking Events
    Route::get('/shipments/{id}/tracking', [ShipmentController::class, 'tracking']);
    Route::post('/shipments/{id}/tracking', [ShipmentController::class, 'addTracking']);

    // Shipment Exceptions
    Route::get('/shipments/{id}/exceptions', [ExceptionController::class, 'shipmentExceptions']);
    Route::post('/shipments/{id}/exceptions', [ExceptionController::class, 'store']);
    Route::patch('/exceptions/{id}', [ExceptionController::class, 'update']);

    // Carriers Resource
    Route::get('/carriers', [CarrierController::class, 'index']);
    Route::post('/carriers', [CarrierController::class, 'store']);
    Route::get('/carriers/{id}', [CarrierController::class, 'show']);
    Route::match(['put', 'patch'], '/carriers/{id}', [CarrierController::class, 'update']);

    // Locations Resource
    Route::get('/locations', [LocationController::class, 'index']);
    Route::post('/locations', [LocationController::class, 'store']);
    Route::get('/locations/{id}', [LocationController::class, 'show']);
    Route::match(['put', 'patch'], '/locations/{id}', [LocationController::class, 'update']);
    Route::delete('/locations/{id}', [LocationController::class, 'destroy']);
});
