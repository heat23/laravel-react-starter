<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class FeaturesController extends Controller
{
    public function billing(): Response
    {
        return Inertia::render('Features/Billing', [
            'title' => 'Production-Grade Stripe Billing for Laravel — Feature Overview',
            'metaDescription' => 'Redis-locked Stripe mutations, 4 plan tiers, team seats, dunning emails, and incomplete payment recovery — all included out of the box.',
        ]);
    }

    public function featureFlags(): Response
    {
        return Inertia::render('Features/FeatureFlags', [
            'title' => 'Feature Flags for Laravel SaaS — Toggle Features Per-User and Globally',
            'metaDescription' => '11 built-in feature flags with database overrides, per-user targeting, and a UI to toggle them at runtime. No feature flag service subscription required.',
        ]);
    }

    public function adminPanel(): Response
    {
        return Inertia::render('Features/AdminPanel', [
            'title' => 'Built-in Laravel Admin Panel — User Management, Billing, Health Monitoring',
            'metaDescription' => 'A full React + TypeScript admin panel: user management, subscription oversight, audit logs, feature flag toggles, health checks, and config viewer — no Filament required.',
        ]);
    }
}
