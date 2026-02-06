<?php

use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Billing\PricingController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\OnboardingController;
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

// Onboarding (route always registered; middleware checks feature flag)
Route::middleware('auth')->group(function () {
    Route::get('/onboarding', OnboardingController::class)->name('onboarding');
});

// Dashboard (requires auth + verification if enabled + onboarding middleware)
Route::middleware(array_filter([
    'auth',
    config('features.email_verification.enabled', true) ? 'verified' : null,
    'onboarding',
]))->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/charts', [ChartsController::class, 'index'])->name('dashboard.charts');
});

// Export routes
Route::middleware(['auth', 'throttle:10,1'])->group(function () {
    Route::get('/export/users', [ExportController::class, 'users'])->name('export.users');
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
