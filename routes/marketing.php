<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\BuyController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactSalesController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\GuidesController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\RoadmapController;
use App\Http\Controllers\UnsubscribeController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

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

Route::get('/features', [FeaturesController::class, 'index'])->name('features.index');
Route::get('/features/billing', [FeaturesController::class, 'billing'])->name('features.billing');
Route::get('/features/feature-flags', [FeaturesController::class, 'featureFlags'])->name('features.feature-flags');
Route::get('/features/admin-panel', [FeaturesController::class, 'adminPanel'])->name('features.admin-panel');
Route::get('/features/webhooks', [FeaturesController::class, 'webhooks'])->name('features.webhooks');
Route::get('/features/two-factor-auth', [FeaturesController::class, 'twoFactor'])->name('features.two-factor-auth');
Route::get('/features/social-auth', [FeaturesController::class, 'socialAuth'])->name('features.social-auth');

Route::get('/guides', [GuidesController::class, 'index'])->name('guides.index');
Route::get('/guides/building-saas-with-laravel-12', [GuidesController::class, 'laravelSaasGuide'])->name('guides.laravel-saas');
Route::get('/guides/laravel-stripe-billing-tutorial', [GuidesController::class, 'stripeGuide'])->name('guides.stripe-guide');
Route::get('/guides/laravel-feature-flags-tutorial', [GuidesController::class, 'featureFlagsGuide'])->name('guides.feature-flags-guide');
Route::get('/guides/saas-starter-kit-comparison-2026', [GuidesController::class, 'saasStarterKitComparison'])->name('guides.saas-starter-kit-comparison');
Route::get('/guides/cost-of-building-saas-from-scratch', [GuidesController::class, 'buildVsBuyGuide'])->name('guides.build-vs-buy');
Route::get('/guides/laravel-two-factor-authentication', [GuidesController::class, 'twoFactorGuide'])->name('guides.two-factor');
Route::get('/guides/laravel-webhook-implementation', [GuidesController::class, 'webhookGuide'])->name('guides.webhook');
Route::get('/guides/single-tenant-vs-multi-tenant-saas', [GuidesController::class, 'tenancyArchitectureGuide'])->name('guides.tenancy-architecture');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show')->where('slug', '[a-z0-9-]+');

Route::get('/unsubscribe/{userId}', [UnsubscribeController::class, 'unsubscribe'])
    ->whereNumber('userId')
    ->middleware('throttle:10,1')
    ->name('unsubscribe');
