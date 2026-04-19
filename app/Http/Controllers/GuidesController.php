<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

// Using /guides/ namespace for long-form pillar content, separate from shorter blog posts.
// BlogController handles /blog/ routes with Markdown files in resources/content/blog/.
class GuidesController extends Controller
{
    public function index(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/Index', [
            'title' => 'Laravel SaaS Guides — Tutorials, Architecture, and Best Practices',
            'metaDescription' => 'In-depth guides for building production Laravel SaaS: Stripe billing, feature flags, architecture decisions, and cost analysis. Free, no signup required.',
            'canonicalUrl' => $appUrl.'/guides',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
            ],
            'guides' => [
                [
                    'title' => 'Complete Guide to Building a SaaS with Laravel 12',
                    'description' => 'Auth, billing, admin panel, feature flags, webhooks, and testing — with code examples and a working starter kit.',
                    'href' => '/guides/building-saas-with-laravel-12',
                    'readingTime' => '25 min read',
                ],
                [
                    'title' => 'Laravel Stripe Billing Tutorial',
                    'description' => 'Subscriptions with Cashier, webhook handling, race condition prevention with Redis locks, and dunning emails.',
                    'href' => '/guides/laravel-stripe-billing-tutorial',
                    'readingTime' => '18 min read',
                ],
                [
                    'title' => 'Laravel Feature Flags — Runtime Toggles Without Unleash',
                    'description' => 'Env-based toggles, database overrides, per-user targeting, and a React UI for runtime control.',
                    'href' => '/guides/laravel-feature-flags-tutorial',
                    'readingTime' => '14 min read',
                ],
                [
                    'title' => 'Best Laravel SaaS Starter Kits 2026',
                    'description' => 'Comparison of 8 Laravel SaaS boilerplates with feature matrix, pricing, and honest pros/cons.',
                    'href' => '/guides/saas-starter-kit-comparison-2026',
                    'readingTime' => '20 min read',
                ],
                [
                    'title' => 'True Cost of Building SaaS from Scratch in 2026',
                    'description' => 'Building a SaaS from scratch costs 200–400 developer hours. See the full breakdown vs using a starter kit.',
                    'href' => '/guides/cost-of-building-saas-from-scratch',
                    'readingTime' => '12 min read',
                ],
            ],
        ]);
    }

    public function laravelSaasGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/LaravelSaasGuide', [
            'title' => 'Complete Guide to Building a SaaS with Laravel 12 in 2026',
            'metaDescription' => 'Everything you need to ship a production Laravel 12 SaaS: auth, billing, admin panel, feature flags, webhooks, and testing — with code examples and a working starter kit.',
            'appName' => $appName,
            'canonicalUrl' => $appUrl.'/guides/building-saas-with-laravel-12',
            'ogImage' => asset('og/guide-laravel-saas.png'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
                ['name' => 'Building a SaaS with Laravel 12', 'url' => $appUrl.'/guides/building-saas-with-laravel-12'],
            ],
        ]);
    }

    public function stripeGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/StripeBillingGuide', [
            'title' => 'Laravel Stripe Billing Tutorial — Subscriptions & Race Conditions',
            'metaDescription' => 'How to implement production-grade Stripe billing in Laravel 12: subscriptions with Cashier, webhook handling, race condition prevention with Redis locks, and dunning emails.',
            'appName' => $appName,
            'canonicalUrl' => $appUrl.'/guides/laravel-stripe-billing-tutorial',
            'ogImage' => asset('og/guide-stripe-billing.png'),
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
            'title' => 'Laravel Feature Flags — Runtime Toggles Without Unleash',
            'metaDescription' => 'Implement feature flags in Laravel without a third-party service: env-based toggles, database overrides, per-user targeting, and a React UI for runtime control.',
            'appName' => $appName,
            'canonicalUrl' => $appUrl.'/guides/laravel-feature-flags-tutorial',
            'ogImage' => asset('og/guide-feature-flags.png'),
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
            'canonicalUrl' => $appUrl.'/guides/saas-starter-kit-comparison-2026',
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
            'canonicalUrl' => $appUrl.'/guides/cost-of-building-saas-from-scratch',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
                ['name' => 'True Cost of Building SaaS from Scratch', 'url' => $appUrl.'/guides/cost-of-building-saas-from-scratch'],
            ],
        ]);
    }

    public function twoFactorGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/TwoFactorGuide', [
            'title' => 'Laravel Two-Factor Authentication Guide 2026',
            'metaDescription' => 'Step-by-step guide to adding TOTP-based 2FA in Laravel using laragear/two-factor. Includes recovery codes, React UI, and Pest tests. 2026 edition.',
            'appName' => $appName,
            'appUrl' => $appUrl,
            'canonicalUrl' => $appUrl.'/guides/laravel-two-factor-authentication',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
                ['name' => 'Laravel Two-Factor Authentication', 'url' => $appUrl.'/guides/laravel-two-factor-authentication'],
            ],
        ]);
    }

    public function webhookGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/WebhookGuide', [
            'title' => 'Laravel Webhook Implementation Guide 2026',
            'metaDescription' => 'How to add production-grade outgoing webhooks to Laravel SaaS: HMAC-SHA256 signing, queue-based retry, and delivery tracking. With Pest tests. 2026 guide.',
            'appName' => $appName,
            'appUrl' => $appUrl,
            'canonicalUrl' => $appUrl.'/guides/laravel-webhook-implementation',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
                ['name' => 'Laravel Webhook Implementation', 'url' => $appUrl.'/guides/laravel-webhook-implementation'],
            ],
        ]);
    }

    public function tenancyArchitectureGuide(): Response
    {
        $appName = config('app.name', 'Laravel React Starter');
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Guides/TenancyArchitectureGuide', [
            'title' => 'Single-Tenant vs Multi-Tenant SaaS — Architecture Guide 2026',
            'metaDescription' => 'When should you choose single-tenant vs multi-tenant for your SaaS? Honest comparison of complexity, cost, and performance tradeoffs for Laravel apps in 2026.',
            'appName' => $appName,
            'canonicalUrl' => $appUrl.'/guides/single-tenant-vs-multi-tenant-saas',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Guides', 'url' => $appUrl.'/guides'],
                ['name' => 'Single-Tenant vs Multi-Tenant SaaS', 'url' => $appUrl.'/guides/single-tenant-vs-multi-tenant-saas'],
            ],
        ]);
    }
}
