<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\UserSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User settings API (for theme persistence, etc.)
if (config('features.user_settings.enabled', true)) {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/settings', [UserSettingsController::class, 'index']);
        Route::post('/settings', [UserSettingsController::class, 'store'])->middleware('throttle:30,1');
    });
}

// Notifications API (feature-gated in controller constructor)
Route::middleware(['auth:sanctum'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->middleware('throttle:30,1');
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->middleware('throttle:60,1');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->middleware('throttle:10,1');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->middleware('throttle:30,1');
});

// API token management (for users managing their own tokens)
if (config('features.api_tokens.enabled', true)) {
    Route::middleware(['auth:sanctum', 'throttle:20,1'])->prefix('tokens')->group(function () {
        Route::get('/', [TokenController::class, 'index']);
        Route::post('/', [TokenController::class, 'store']);
        Route::delete('/{tokenId}', [TokenController::class, 'destroy']);
    });
}
