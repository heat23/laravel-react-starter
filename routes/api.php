<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\UserSettingsController;
use App\Http\Controllers\Api\WebhookEndpointController;
use App\Http\Controllers\Webhook\IncomingWebhookController;
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

/**
 * @group Authentication
 *
 * @authenticated
 *
 * @response 200 {"id":1,"name":"John Doe","email":"john@example.com","email_verified_at":"2026-01-01T00:00:00.000000Z"}
 */
Route::middleware(['auth:sanctum', 'throttle:60,1'])->get('/user', function (Request $request) {
    $user = $request->user();

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'email_verified_at' => $user->email_verified_at?->toISOString(),
    ]);
});

// User settings API (for theme persistence, etc.)
if (config('features.user_settings.enabled', true)) {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/settings', [UserSettingsController::class, 'index'])->middleware('throttle:60,1');
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

// Webhook endpoint management (feature-gated in controller constructor)
Route::middleware(['auth:sanctum', 'throttle:30,1'])->prefix('webhooks')->group(function () {
    Route::get('/', [WebhookEndpointController::class, 'index']);
    Route::post('/', [WebhookEndpointController::class, 'store']);
    Route::get('/{endpointId}', [WebhookEndpointController::class, 'show']);
    Route::patch('/{endpointId}', [WebhookEndpointController::class, 'update']);
    Route::delete('/{endpointId}', [WebhookEndpointController::class, 'destroy']);
    Route::get('/{endpointId}/deliveries', [WebhookEndpointController::class, 'deliveries']);
});

// Webhook test dispatch — stricter rate limit (5/min) to prevent flooding
Route::middleware(['auth:sanctum', 'throttle:webhook-test'])->prefix('webhooks')->group(function () {
    Route::post('/{endpointId}/test', [WebhookEndpointController::class, 'test']);
});

// Incoming webhooks (signature-verified, no auth required)
Route::prefix('webhooks/incoming')->group(function () {
    Route::post('/{provider}', [IncomingWebhookController::class, 'handle'])
        ->middleware(['verify-webhook', 'throttle:120,1'])
        ->name('webhooks.incoming');
});
