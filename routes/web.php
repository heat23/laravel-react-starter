<?php

use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Billing\PricingController;
use App\Http\Controllers\Billing\StripeWebhookController;
use App\Http\Controllers\Billing\SubscriptionController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\Settings\ApiTokenPageController;
use App\Http\Controllers\Settings\TwoFactorController;
use App\Http\Controllers\Settings\WebhookPageController;
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
Route::get('/sitemap.xml', function () {
    return response()->view('sitemap')->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::get('/robots.txt', function () {
    $content = match (app()->environment()) {
        'production' => "User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml'),
        default => "User-agent: *\nDisallow: /",
    };
    return response($content)->header('Content-Type', 'text/plain');
})->name('robots');

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
    // Public pricing page
    Route::get('/pricing', PricingController::class)->name('pricing');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/billing/subscribe', [SubscriptionController::class, 'subscribe'])->middleware('throttle:5,1')->name('billing.subscribe');
        Route::post('/billing/cancel', [SubscriptionController::class, 'cancel'])->middleware('throttle:5,1')->name('billing.cancel');
        Route::post('/billing/resume', [SubscriptionController::class, 'resume'])->middleware('throttle:5,1')->name('billing.resume');
        Route::post('/billing/swap', [SubscriptionController::class, 'swap'])->middleware('throttle:5,1')->name('billing.swap');
        Route::post('/billing/quantity', [SubscriptionController::class, 'updateQuantity'])->middleware('throttle:5,1')->name('billing.quantity');
        Route::post('/billing/payment-method', [SubscriptionController::class, 'updatePaymentMethod'])->middleware('throttle:5,1')->name('billing.payment-method');
        Route::get('/billing/portal', [SubscriptionController::class, 'portal'])->name('billing.portal');
    });

    // Stripe webhook (no auth - Cashier verifies signature)
    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
        ->middleware('throttle:120,1')
        ->name('cashier.webhook');
}

// Two-Factor Authentication settings (feature-gated in controller constructor)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings/security', [TwoFactorController::class, 'index'])
        ->name('settings.security');
    Route::post('/settings/security/enable', [TwoFactorController::class, 'enable'])
        ->name('two-factor.enable');
    Route::post('/settings/security/confirm', [TwoFactorController::class, 'confirm'])
        ->name('two-factor.confirm');
    Route::delete('/settings/security/disable', [TwoFactorController::class, 'disable'])
        ->name('two-factor.disable');
    Route::get('/settings/security/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])
        ->middleware('throttle:5,1')
        ->name('two-factor.recovery-codes');
    Route::post('/settings/security/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])
        ->middleware('throttle:5,1')
        ->name('two-factor.recovery-codes.regenerate');
});

// Webhooks settings (feature-gated in controller constructor)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings/webhooks', WebhookPageController::class)->name('settings.webhooks');
});

// SEO routes
Route::get('/favicon.ico', function () {
    $path = public_path('favicon.ico');
    abort_unless(file_exists($path), 404);

    return response()->file($path, [
        'Cache-Control' => 'public, max-age=604800, immutable',
    ]);
})->name('favicon');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

// Health check (controller handles its own authorization)
Route::get('/health', HealthCheckController::class)->name('health');

// Admin panel (optional feature)
if (config('features.admin.enabled', false)) {
    require __DIR__.'/admin.php';
}

require __DIR__.'/auth.php';
