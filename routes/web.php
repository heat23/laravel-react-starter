<?php

use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Billing\PricingController;
use App\Http\Controllers\Billing\StripeWebhookController;
use App\Http\Controllers\Billing\SubscriptionController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BuyController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactSalesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GuidesController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\NpsSurveyController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PersonalDataExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\Settings\ApiTokenPageController;
use App\Http\Controllers\Settings\TwoFactorController;
use App\Http\Controllers\Settings\WebhookPageController;
use App\Http\Controllers\UnsubscribeController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\PaymentController;

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
Route::get('/buy', [BuyController::class, 'show'])->name('buy');
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/about', [LegalController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'show'])->middleware('throttle:10,1')->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::post('/contact/sales', ContactSalesController::class)->middleware('throttle:3,1')->name('contact.sales');
Route::get('/changelog', [ChangelogController::class, 'index'])->name('changelog');
Route::post('/changelog/acknowledge', [ChangelogController::class, 'acknowledge'])->middleware(['auth', 'throttle:10,1'])->name('changelog.acknowledge');
Route::get('/roadmap', [RoadmapController::class, 'index'])->name('roadmap');
Route::post('/roadmap/{slug}/vote', [RoadmapController::class, 'vote'])->middleware(['auth', 'throttle:60,1'])->name('roadmap.vote');

// Competitor comparison pages (SEO landing pages)
Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
Route::get('/compare/laravel-jetstream', [CompareController::class, 'jetstream'])->name('compare.jetstream');
Route::get('/compare/laravel-spark', [CompareController::class, 'spark'])->name('compare.spark');
Route::get('/compare/saasykit', [CompareController::class, 'saasykit'])->name('compare.saasykit');
Route::get('/compare/wave', [CompareController::class, 'wave'])->name('compare.wave');
Route::get('/compare/shipfast', [CompareController::class, 'shipfast'])->name('compare.shipfast');
Route::get('/compare/supastarter', [CompareController::class, 'supastarter'])->name('compare.supastarter');
Route::get('/compare/larafast', [CompareController::class, 'larafast'])->name('compare.larafast');
Route::get('/compare/makerkit', [CompareController::class, 'makerkit'])->name('compare.makerkit');
Route::get('/compare/laravel-vs-nextjs', [CompareController::class, 'nextjsSaas'])->name('compare.nextjs-saas');

// Feature landing pages (SEO) — always registered regardless of feature flags
Route::get('/features', [FeaturesController::class, 'index'])->name('features.index');
Route::get('/features/billing', [FeaturesController::class, 'billing'])->name('features.billing');
Route::get('/features/feature-flags', [FeaturesController::class, 'featureFlags'])->name('features.feature-flags');
Route::get('/features/admin-panel', [FeaturesController::class, 'adminPanel'])->name('features.admin-panel');
Route::get('/features/webhooks', [FeaturesController::class, 'webhooks'])->name('features.webhooks');
Route::get('/features/two-factor-auth', [FeaturesController::class, 'twoFactor'])->name('features.two-factor-auth');
Route::get('/features/social-auth', [FeaturesController::class, 'socialAuth'])->name('features.social-auth');

// Guides index + individual guides
Route::get('/guides', [GuidesController::class, 'index'])->name('guides.index');
Route::get('/guides/building-saas-with-laravel-12', [GuidesController::class, 'laravelSaasGuide'])->name('guides.laravel-saas');
Route::get('/guides/laravel-stripe-billing-tutorial', [GuidesController::class, 'stripeGuide'])->name('guides.stripe-guide');
Route::get('/guides/laravel-feature-flags-tutorial', [GuidesController::class, 'featureFlagsGuide'])->name('guides.feature-flags-guide');
Route::get('/guides/saas-starter-kit-comparison-2026', [GuidesController::class, 'saasStarterKitComparison'])->name('guides.saas-starter-kit-comparison');
Route::get('/guides/cost-of-building-saas-from-scratch', [GuidesController::class, 'buildVsBuyGuide'])->name('guides.build-vs-buy');
Route::get('/guides/laravel-two-factor-authentication', [GuidesController::class, 'twoFactorGuide'])->name('guides.two-factor');
Route::get('/guides/laravel-webhook-implementation', [GuidesController::class, 'webhookGuide'])->name('guides.webhook');
Route::get('/guides/single-tenant-vs-multi-tenant-saas', [GuidesController::class, 'tenancyArchitectureGuide'])->name('guides.tenancy-architecture');

// Blog (Markdown-based content at resources/content/blog/)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show')->where('slug', '[a-z0-9-]+');

// Feedback (authenticated users only)
Route::post('/feedback', [FeedbackController::class, 'store'])->middleware(['auth', 'throttle:10,1'])->name('feedback.store');

// NPS Survey
Route::middleware(['auth'])->group(function () {
    Route::get('/nps/eligible', [NpsSurveyController::class, 'eligible'])->name('nps.eligible');
    Route::post('/nps', [NpsSurveyController::class, 'store'])->middleware('throttle:5,1')->name('nps.store');
});

// Email unsubscribe (no auth — signed URL provides security)
Route::get('/unsubscribe/{userId}', [UnsubscribeController::class, 'unsubscribe'])
    ->whereNumber('userId')
    ->middleware('throttle:10,1')
    ->name('unsubscribe');

// Onboarding (route always registered; middleware checks feature flag)
Route::middleware('auth')->group(function () {
    Route::get('/onboarding', OnboardingController::class)->name('onboarding');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
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

// Personal data export (GDPR Article 15/20 — stricter rate limit)
Route::middleware(['auth', 'verified', 'throttle:3,60'])->group(function () {
    Route::get('/export/personal-data', PersonalDataExportController::class)->name('export.personal-data');
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
        Route::post('/billing/checkout', [SubscriptionController::class, 'checkout'])->middleware('throttle:5,1')->name('billing.checkout');
        Route::post('/billing/subscribe', [SubscriptionController::class, 'subscribe'])->middleware('throttle:5,1')->name('billing.subscribe');
        Route::post('/billing/cancel', [SubscriptionController::class, 'cancel'])->middleware('throttle:5,1')->name('billing.cancel');
        Route::post('/billing/resume', [SubscriptionController::class, 'resume'])->middleware('throttle:5,1')->name('billing.resume');
        Route::get('/billing/swap/preview', [SubscriptionController::class, 'swapPreview'])->middleware('throttle:20,1')->name('billing.swap.preview');
        Route::post('/billing/swap', [SubscriptionController::class, 'swap'])->middleware('throttle:5,1')->name('billing.swap');
        Route::post('/billing/quantity', [SubscriptionController::class, 'updateQuantity'])->middleware('throttle:5,1')->name('billing.quantity');
        Route::post('/billing/payment-method', [SubscriptionController::class, 'updatePaymentMethod'])->middleware('throttle:5,1')->name('billing.payment-method');
        Route::post('/billing/retention-coupon', [SubscriptionController::class, 'applyRetentionCoupon'])->middleware('throttle:3,60')->name('billing.retention-coupon');
        Route::get('/billing/portal', [SubscriptionController::class, 'portal'])->name('billing.portal');
    });

    // Cashier payment confirmation page (SCA/3DS redirect target)
    Route::get('/stripe/payment/{id}', PaymentController::class.'@show')
        ->name('cashier.payment');

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
Route::get('/llms.txt', [SeoController::class, 'llms'])->name('llms');

// Health check (controller handles its own authorization)
Route::get('/health', HealthCheckController::class)->name('health');

// Admin panel (optional feature)
if (config('features.admin.enabled', false)) {
    require __DIR__.'/admin.php';
}

require __DIR__.'/auth.php';
