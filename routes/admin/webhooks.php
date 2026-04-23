<?php

// Inherited middleware: auth, verified, admin, throttle:60,1,admin:
// Permission: admin (read); super_admin (restore); feature-gated by features.webhooks.enabled

use App\Http\Controllers\Admin\AdminWebhooksController;
use Illuminate\Support\Facades\Route;

if (config('features.webhooks.enabled')) {
    Route::get('/webhooks', AdminWebhooksController::class)
        ->middleware('throttle:admin-read')
        ->name('webhooks');
    Route::get('/webhooks/incoming', [AdminWebhooksController::class, 'incomingWebhooks'])
        ->middleware('throttle:admin-read')
        ->name('webhooks.incoming');
    Route::get('/webhooks/endpoints', [AdminWebhooksController::class, 'endpoints'])
        ->middleware('throttle:admin-read')
        ->name('webhooks.endpoints');
    Route::patch('/webhooks/endpoints/{id}/restore', [AdminWebhooksController::class, 'restoreEndpoint'])
        ->middleware(['throttle:admin-write', 'super_admin'])
        ->name('webhooks.endpoints.restore');
    Route::get('/webhooks/deliveries/{id}', [AdminWebhooksController::class, 'showDelivery'])
        ->middleware('throttle:admin-read')
        ->name('webhooks.deliveries.show');
}
