<?php

// Inherited middleware: auth, verified, admin, throttle:60,1,admin:
// Permission: admin (read); feature-gated by features.billing.enabled and features.api_tokens.enabled

use App\Http\Controllers\Admin\AdminBillingController;
use App\Http\Controllers\Admin\AdminTokensController;
use Illuminate\Support\Facades\Route;

if (config('features.billing.enabled')) {
    Route::get('/billing', [AdminBillingController::class, 'dashboard'])
        ->middleware('throttle:admin-read')
        ->name('billing.dashboard');
    Route::get('/billing/subscriptions/export', [AdminBillingController::class, 'export'])
        ->middleware('throttle:admin-write')
        ->name('billing.subscriptions.export');
    Route::get('/billing/subscriptions', [AdminBillingController::class, 'subscriptions'])
        ->middleware('throttle:admin-read')
        ->name('billing.subscriptions');
    Route::get('/billing/subscriptions/{subscription}', [AdminBillingController::class, 'show'])
        ->middleware('throttle:admin-read')
        ->name('billing.show');
}

if (config('features.api_tokens.enabled')) {
    Route::get('/tokens', AdminTokensController::class)
        ->middleware('throttle:admin-read')
        ->name('tokens');
    Route::get('/tokens/export', [AdminTokensController::class, 'export'])
        ->middleware('throttle:admin-write')
        ->name('tokens.export');
    Route::get('/tokens/list', [AdminTokensController::class, 'index'])
        ->middleware('throttle:admin-read')
        ->name('tokens.index');
    Route::delete('/tokens/{id}', [AdminTokensController::class, 'revoke'])
        ->middleware(['throttle:admin-write', 'super_admin'])
        ->name('tokens.revoke');
}
