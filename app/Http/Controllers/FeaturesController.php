<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class FeaturesController extends Controller
{
    public function index(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Features/Index', [
            'title' => 'All Features — Laravel React SaaS Starter Kit',
            'metaDescription' => 'Production-grade billing, feature flags, admin panel, webhooks, 2FA, and social auth. All included in one Laravel React starter kit.',
            'canonicalUrl' => $appUrl.'/features',
            'ogImage' => asset('og/features-index.png'),
            'canRegister' => Route::has('register'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features'],
            ],
            'features' => [
                [
                    'title' => 'Production-Grade Stripe Billing',
                    'description' => 'Redis-locked mutations, 4 plan tiers, team seats, dunning emails, and incomplete payment recovery.',
                    'href' => '/features/billing',
                ],
                [
                    'title' => 'Feature Flags',
                    'description' => '11 built-in flags with database overrides, per-user targeting, and admin UI — no third-party service.',
                    'href' => '/features/feature-flags',
                ],
                [
                    'title' => 'Admin Panel',
                    'description' => 'Full React + TypeScript admin: user management, billing oversight, audit logs, health monitoring.',
                    'href' => '/features/admin-panel',
                ],
                [
                    'title' => 'Webhooks',
                    'description' => 'Outgoing webhooks with HMAC-SHA256 signing, async dispatch, delivery tracking, and incoming webhook processing.',
                    'href' => '/features/webhooks',
                ],
                [
                    'title' => 'Two-Factor Authentication',
                    'description' => 'TOTP 2FA via laragear/two-factor with recovery codes, setup UI, and optional enforcement.',
                    'href' => '/features/two-factor-auth',
                ],
                [
                    'title' => 'Social Auth',
                    'description' => 'Google + GitHub OAuth via Socialite with account linking and existing account detection.',
                    'href' => '/features/social-auth',
                ],
            ],
        ]);
    }

    public function billing(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Features/Billing', [
            'title' => 'Production-Grade Stripe Billing for Laravel — Feature Overview',
            'metaDescription' => 'Redis-locked Stripe mutations, 4 plan tiers, team seats, dunning emails, and incomplete payment recovery — all included out of the box.',
            'canonicalUrl' => $appUrl.'/features/billing',
            'ogImage' => asset('og/features-billing.png'),
            'canRegister' => Route::has('register'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features'],
                ['name' => 'Billing', 'url' => $appUrl.'/features/billing'],
            ],
        ]);
    }

    public function featureFlags(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Features/FeatureFlags', [
            'title' => 'Feature Flags for Laravel SaaS — Toggle Features Per-User and Globally',
            'metaDescription' => '11 built-in feature flags with database overrides, per-user targeting, and a UI to toggle them at runtime. No feature flag service subscription required.',
            'canonicalUrl' => $appUrl.'/features/feature-flags',
            'ogImage' => asset('og/features-feature-flags.png'),
            'canRegister' => Route::has('register'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features'],
                ['name' => 'Feature Flags', 'url' => $appUrl.'/features/feature-flags'],
            ],
        ]);
    }

    public function adminPanel(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Features/AdminPanel', [
            'title' => 'Built-in Laravel Admin Panel — User Management, Billing, Health Monitoring',
            'metaDescription' => 'A full React + TypeScript admin panel: user management, subscription oversight, audit logs, feature flag toggles, health checks, and config viewer — no Filament required.',
            'canonicalUrl' => $appUrl.'/features/admin-panel',
            'ogImage' => asset('og/features-admin-panel.png'),
            'canRegister' => Route::has('register'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features'],
                ['name' => 'Admin Panel', 'url' => $appUrl.'/features/admin-panel'],
            ],
        ]);
    }

    public function webhooks(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Features/Webhooks', [
            'title' => 'Laravel Webhooks — Outgoing + Incoming HMAC-Signed Webhook Delivery',
            'metaDescription' => 'Production-grade outgoing webhooks with HMAC-SHA256 signing, async dispatch via jobs, delivery tracking table, and incoming webhook processing for GitHub and Stripe.',
            'canonicalUrl' => $appUrl.'/features/webhooks',
            'ogImage' => asset('og/features-webhooks.png'),
            'canRegister' => Route::has('register'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features'],
                ['name' => 'Webhooks', 'url' => $appUrl.'/features/webhooks'],
            ],
        ]);
    }

    public function twoFactor(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Features/TwoFactorAuth', [
            'title' => 'Laravel Two-Factor Authentication — TOTP 2FA with Recovery Codes',
            'metaDescription' => 'TOTP two-factor authentication via laragear/two-factor. Setup wizard, recovery codes, enforcement policies, and a React settings UI — all included.',
            'canonicalUrl' => $appUrl.'/features/two-factor-auth',
            'ogImage' => asset('og/features-two-factor-auth.png'),
            'canRegister' => Route::has('register'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features'],
                ['name' => 'Two-Factor Auth', 'url' => $appUrl.'/features/two-factor-auth'],
            ],
        ]);
    }

    public function socialAuth(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Features/SocialAuth', [
            'title' => 'Laravel Social Auth — Google + GitHub OAuth via Socialite',
            'metaDescription' => 'Google and GitHub OAuth sign-in via Laravel Socialite. Auto-detects providers by env vars, handles account linking, and supports both new and existing accounts.',
            'canonicalUrl' => $appUrl.'/features/social-auth',
            'ogImage' => asset('og/features-social-auth.png'),
            'canRegister' => Route::has('register'),
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features'],
                ['name' => 'Social Auth', 'url' => $appUrl.'/features/social-auth'],
            ],
        ]);
    }
}
