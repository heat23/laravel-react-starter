<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

// Using /guides/ namespace for long-form pillar content, separate from shorter blog posts.
// If a multi-article blog is added later, use a BlogController with /blog/ routes
// and consider Option B (Markdown files parsed by league/commonmark) for content delivery.
class GuidesController extends Controller
{
    public function laravelSaasGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/LaravelSaasGuide', [
            'title' => 'Complete Guide to Building a SaaS with Laravel 12 in 2026',
            'metaDescription' => 'Everything you need to ship a production Laravel 12 SaaS: auth, billing, admin panel, feature flags, webhooks, and testing — with code examples and a working starter kit.',
            'appName' => $appName,
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides/building-saas-with-laravel-12'],
                ['name' => 'Building a SaaS with Laravel 12', 'url' => $appUrl.'/guides/building-saas-with-laravel-12'],
            ],
        ]);
    }

    public function stripeGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/StripeBillingGuide', [
            'title' => 'Laravel Stripe Billing Tutorial — Subscriptions, Webhooks, and Race Conditions',
            'metaDescription' => 'How to implement production-grade Stripe billing in Laravel 12: subscriptions with Cashier, webhook handling, race condition prevention with Redis locks, and dunning emails.',
            'appName' => $appName,
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
                ['name' => 'Laravel Stripe Billing', 'url' => $appUrl.'/guides/laravel-stripe-billing-tutorial'],
            ],
        ]);
    }

    public function featureFlagsGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/FeatureFlagsGuide', [
            'title' => 'Laravel Feature Flags — Runtime Toggles Without Unleash or LaunchDarkly',
            'metaDescription' => 'Implement feature flags in Laravel without a third-party service: env-based toggles, database overrides, per-user targeting, and a React UI for runtime control.',
            'appName' => $appName,
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
                ['name' => 'Laravel Feature Flags', 'url' => $appUrl.'/guides/laravel-feature-flags-tutorial'],
            ],
        ]);
    }

    public function saasStarterKitComparison(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/SaasStarterKitComparison', [
            'title' => 'Best Laravel SaaS Starter Kits 2026 — Ranked & Reviewed',
            'metaDescription' => 'Comparison of 8 Laravel SaaS boilerplates with feature matrix, pricing, and honest pros/cons. Updated March 2026.',
            'appName' => $appName,
            'appUrl' => $appUrl,
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
                ['name' => 'SaaS Starter Kit Comparison 2026', 'url' => $appUrl.'/guides/saas-starter-kit-comparison-2026'],
            ],
        ]);
    }

    public function buildVsBuyGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/BuildVsBuyGuide', [
            'title' => 'True Cost of Building SaaS from Scratch in 2026',
            'metaDescription' => 'Building a SaaS from scratch costs 200–400 developer hours before you write a line of business logic. See the full breakdown vs using a starter kit.',
            'appName' => $appName,
            'appUrl' => $appUrl,
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
                ['name' => 'True Cost of Building SaaS from Scratch', 'url' => $appUrl.'/guides/cost-of-building-saas-from-scratch'],
            ],
        ]);
    }
}
