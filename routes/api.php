
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\VehicleTypeController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\TripHistoryController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\CompanyLocationController;
use App\Http\Controllers\Api\TripNotificationController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\DashboardController;

// ==========================================
// AUTHENTICATION
// ==========================================

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // ======================================
    // DRIVER MANAGEMENT (HR)
    // ======================================

    Route::middleware('role:hr')->group(function () {
        Route::get('/drivers', [DriverController::class, 'index']);
        Route::post('/drivers', [DriverController::class, 'store']);
        Route::get('/drivers/{id}', [DriverController::class, 'show']);
        Route::put('/drivers/{id}', [DriverController::class, 'update']);
        Route::patch('/drivers/{id}/status', [DriverController::class, 'updateStatus']);
    });

    // ======================================
    // VEHICLE TYPES (HR)
    // ======================================

    Route::middleware('role:hr')->group(function () {
        Route::get('/vehicle-types', [VehicleTypeController::class, 'index']);
        Route::post('/vehicle-types', [VehicleTypeController::class, 'store']);
        Route::put('/vehicle-types/{id}', [VehicleTypeController::class, 'update']);
        Route::delete('/vehicle-types/{id}', [VehicleTypeController::class, 'destroy']);
    });

    // ======================================
    // VEHICLES
    // ======================================

    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{id}', [VehicleController::class, 'show']);

    Route::middleware('role:hr')->group(function () {
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::put('/vehicles/{id}', [VehicleController::class, 'update']);
        Route::patch('/vehicles/{id}/status', [VehicleController::class, 'updateStatus']);
    });

    // ======================================
    // TRIPS
    // ======================================

    Route::get('/trips', [TripController::class, 'index']);
    Route::post('/trips', [TripController::class, 'store']);
    Route::get('/trips/{id}', [TripController::class, 'show']);

    Route::post('/trips/{id}/start', [TripController::class, 'start']);
    Route::post('/trips/{id}/complete', [TripController::class, 'complete']);

    Route::patch('/trips/{id}/correct-time', [TripController::class, 'correctTime']);

    // ======================================
    // DRIVER TRIP HISTORY
    // ======================================

    Route::delete(
        '/driver/trip-history/{id}',
        [TripHistoryController::class, 'destroy']
    );

    // ======================================
    // GPS TRACKING
    // ======================================

    Route::post('/trips/{tripId}/locations', [TrackingController::class, 'storeLocation']);
    Route::get('/tracking/live', [TrackingController::class, 'live']);

    Route::post('/stops/{stopId}/arrive', [TrackingController::class, 'arriveAtStop']);
    Route::post('/stops/{stopId}/depart', [TrackingController::class, 'departFromStop']);

    Route::get('/geofence-locations', [TrackingController::class, 'geofenceLocations']);

    // ======================================
    // COMPANY LOCATIONS (HR)
    // ======================================

    Route::middleware('role:hr')->group(function () {
        Route::get('/company-locations', [CompanyLocationController::class, 'index']);
        Route::post('/company-locations', [CompanyLocationController::class, 'store']);
        Route::put('/company-locations/{id}', [CompanyLocationController::class, 'update']);
    });

    // ======================================
    // NOTIFICATIONS
    // ======================================

    Route::get('/notifications', [TripNotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [TripNotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [TripNotificationController::class, 'markAllAsRead']);

    // ======================================
    // DEVICE TOKEN
    // ======================================

    Route::post('/device-token', [DeviceTokenController::class, 'store']);
    Route::delete('/device-token', [DeviceTokenController::class, 'destroy']);

    // ======================================
    // HR DASHBOARD
    // ======================================

    Route::middleware('role:hr')->group(function () {
        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    });

});
