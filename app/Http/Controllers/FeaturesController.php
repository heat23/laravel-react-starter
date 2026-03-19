<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class FeaturesController extends Controller
{
    public function billing(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('Features/Billing', [
            'title' => 'Production-Grade Stripe Billing for Laravel — Feature Overview',
            'metaDescription' => 'Redis-locked Stripe mutations, 4 plan tiers, team seats, dunning emails, and incomplete payment recovery — all included out of the box.',
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features/billing'],
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
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features/feature-flags'],
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
            'breadcrumbs' => [
                ['name' => 'Home', 'url' => $appUrl],
                ['name' => 'Features', 'url' => $appUrl.'/features/admin-panel'],
                ['name' => 'Admin Panel', 'url' => $appUrl.'/features/admin-panel'],
            ],
        ]);
    }
}
