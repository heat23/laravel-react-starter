<?php

use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Billing\PricingController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\ApiTokenPageController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Register web routes for your application. These routes are loaded by
| the RouteServiceProvider within a group which contains the "web" middleware.
|
*/

// Public routes
Route::get('/', WelcomeController::class)->name('welcome');

// Dashboard (requires auth + verification if enabled)
Route::middleware(array_filter([
    'auth',
    config('features.email_verification.enabled', true) ? 'verified' : null,
]))->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/charts', [ChartsController::class, 'index'])->name('dashboard.charts');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// API Tokens (optional feature)
if (config('features.api_tokens.enabled', true)) {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/settings/tokens', ApiTokenPageController::class)->name('settings.tokens');
    });
}

// Billing routes (optional feature - requires Laravel Cashier)
if (config('features.billing.enabled', false)) {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::get('/pricing', PricingController::class)->name('pricing');
    });
}

// Health check (controller handles its own authorization)
Route::get('/health', HealthCheckController::class)->name('health');

require __DIR__.'/auth.php';
