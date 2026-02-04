<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

// Dashboard (requires auth + verification if enabled)
Route::middleware(array_filter([
    'auth',
    config('features.email_verification.enabled', true) ? 'verified' : null,
]))->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
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
        Route::get('/settings/tokens', function () {
            return Inertia::render('Settings/ApiTokens');
        })->name('settings.tokens');
    });
}

// Billing routes (optional feature - requires Laravel Cashier)
if (config('features.billing.enabled', false)) {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/billing', function () {
            return Inertia::render('Billing/Index');
        })->name('billing.index');

        Route::get('/pricing', function () {
            return Inertia::render('Pricing');
        })->name('pricing');
    });
}

require __DIR__.'/auth.php';
