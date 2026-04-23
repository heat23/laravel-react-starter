<?php

use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Billing\PaymentMethodController;
use App\Http\Controllers\Billing\PricingController;
use App\Http\Controllers\Billing\RetentionController;
use App\Http\Controllers\Billing\StripeWebhookController;
use App\Http\Controllers\Billing\SubscriptionCheckoutController;
use App\Http\Controllers\Billing\SubscriptionLifecycleController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NpsSurveyController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PersonalDataExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\ApiTokenPageController;
use App\Http\Controllers\Settings\TwoFactorController;
use App\Http\Controllers\Settings\WebhookPageController;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\PaymentController;

Route::post('/feedback', [FeedbackController::class, 'store'])->middleware(['auth', 'throttle:10,1'])->name('feedback.store');

Route::middleware(['auth'])->group(function () {
    Route::get('/nps/eligible', [NpsSurveyController::class, 'eligible'])->name('nps.eligible');
    Route::post('/nps', [NpsSurveyController::class, 'store'])->middleware('throttle:5,1')->name('nps.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/onboarding', OnboardingController::class)->name('onboarding');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
});

Route::middleware(array_filter([
    'auth',
    config('features.email_verification.enabled', true) ? 'verified' : null,
    'onboarding',
]))->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/charts', [ChartsController::class, 'index'])->name('dashboard.charts');
});

Route::middleware(['auth', 'throttle:10,1'])->group(function () {
    Route::get('/export/users', [ExportController::class, 'users'])->name('export.users');
});

Route::middleware(['auth', 'verified', 'throttle:3,60'])->group(function () {
    Route::get('/export/personal-data', PersonalDataExportController::class)->name('export.personal-data');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

if (config('features.api_tokens.enabled', true)) {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/settings/tokens', ApiTokenPageController::class)->name('settings.tokens');
    });
}

if (config('features.billing.enabled', false)) {
    Route::get('/pricing', PricingController::class)->name('pricing');

    Route::middleware(['auth', 'verified', 'billing.context'])->group(function () {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/billing/checkout', [SubscriptionCheckoutController::class, 'checkout'])->middleware('throttle:5,1')->name('billing.checkout');
        Route::post('/billing/subscribe', [SubscriptionCheckoutController::class, 'subscribe'])->middleware('throttle:5,1')->name('billing.subscribe');
        Route::post('/billing/cancel', [SubscriptionLifecycleController::class, 'cancel'])->middleware('throttle:5,1')->name('billing.cancel');
        Route::post('/billing/resume', [SubscriptionLifecycleController::class, 'resume'])->middleware('throttle:5,1')->name('billing.resume');
        Route::get('/billing/swap/preview', [SubscriptionLifecycleController::class, 'swapPreview'])->middleware('throttle:20,1')->name('billing.swap.preview');
        Route::post('/billing/swap', [SubscriptionLifecycleController::class, 'swap'])->middleware('throttle:5,1')->name('billing.swap');
        Route::post('/billing/quantity', [SubscriptionLifecycleController::class, 'updateQuantity'])->middleware('throttle:5,1')->name('billing.quantity');
        Route::post('/billing/payment-method', [PaymentMethodController::class, 'updatePaymentMethod'])->middleware('throttle:5,1')->name('billing.payment-method');
        Route::post('/billing/retention-coupon', [RetentionController::class, 'applyRetentionCoupon'])->middleware('throttle:3,60')->name('billing.retention-coupon');
        Route::get('/billing/portal', [SubscriptionCheckoutController::class, 'portal'])->name('billing.portal');
    });

    Route::get('/stripe/payment/{id}', [PaymentController::class, 'show'])
        ->name('cashier.payment');

    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
        ->middleware('throttle:120,1')
        ->name('cashier.webhook');
}

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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings/webhooks', WebhookPageController::class)->name('settings.webhooks');
});
