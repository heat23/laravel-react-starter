<?php

/**
 * Subscription Plans Configuration
 *
 * Defines limits and features for each subscription tier.
 * Only used when billing feature is enabled.
 *
 * Tier hierarchy (lowest to highest): free < pro < team < enterprise
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Tier Hierarchy
    |--------------------------------------------------------------------------
    |
    | Defines the order of tiers from lowest to highest.
    | Used by EnsureSubscribed middleware for tier-based gating.
    |
    */
    'tier_hierarchy' => ['free', 'pro', 'team', 'enterprise'],

    /*
    |--------------------------------------------------------------------------
    | Free Tier
    |--------------------------------------------------------------------------
    */
    'free' => [
        'name' => 'Free',
        'description' => 'Evaluate the stack before you commit — all features unlocked, limited quotas.',
        'price_monthly' => 0,
        'price_annual' => 0,
        'per_seat' => false,
        'limits' => [
            'projects' => env('PLAN_FREE_PROJECTS', 3),
            'items_per_project' => env('PLAN_FREE_ITEMS', 50),
            'api_tokens' => env('PLAN_FREE_API_TOKENS', 1),
            'history_days' => env('PLAN_FREE_HISTORY_DAYS', 30),
            'seats' => 1,
        ],
        'features' => [
            'Up to 3 projects',
            '30-day activity history',
            'Basic export (CSV)',
            '1 API token',
            'Community support',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pro Tier
    |--------------------------------------------------------------------------
    */
    'pro' => [
        'name' => 'Pro',
        'description' => 'For solo founders shipping a real product — unlimited projects, API access, and priority support.',
        'popular' => true,
        'stripe_price_monthly' => env('STRIPE_PRICE_PRO'),
        'stripe_price_annual' => env('STRIPE_PRICE_PRO_ANNUAL'),
        'price_monthly' => env('PLAN_PRO_PRICE_MONTHLY', 19),
        'price_annual' => env('PLAN_PRO_PRICE_ANNUAL', 182), // $182/yr = 20.2% off ($19 × 12 = $228)
        'per_seat' => false,
        'limits' => [
            'projects' => null, // unlimited
            'items_per_project' => null, // unlimited
            'api_tokens' => env('PLAN_PRO_API_TOKENS', 10),
            'history_days' => env('PLAN_PRO_HISTORY_DAYS', 365),
            'seats' => 1,
        ],
        'features' => [
            'Unlimited projects — never hit a cap',
            '1-year activity history',
            'Advanced export (JSON, CSV)',
            '10 API tokens (1 on Free)',
            'Email notifications',
            'Priority support',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Team Tier
    |--------------------------------------------------------------------------
    */
    'team' => [
        'name' => 'Team',
        'description' => 'For small teams that need shared projects and per-seat billing without enterprise overhead.',
        'stripe_price_monthly' => env('STRIPE_PRICE_TEAM'),
        'stripe_price_annual' => env('STRIPE_PRICE_TEAM_ANNUAL'),
        'price_monthly' => env('PLAN_TEAM_PRICE_MONTHLY', 49),
        'price_annual' => env('PLAN_TEAM_PRICE_ANNUAL', 470), // $470/seat/yr = 20.1% off ($49 × 12 = $588)
        'per_seat' => true,
        'min_seats' => (int) env('PLAN_TEAM_MIN_SEATS', 2),
        'limits' => [
            'projects' => null, // unlimited
            'items_per_project' => null, // unlimited
            'api_tokens' => env('PLAN_TEAM_API_TOKENS', 25),
            'history_days' => null, // unlimited
            'seats' => env('PLAN_TEAM_MAX_SEATS', 50),
        ],
        'features' => [
            'Everything in Pro',
            'Team member management (2–50 seats)',
            'Shared projects with role-based access',
            'Full audit log & compliance exports',
            'Unlimited activity history',
            'Dedicated onboarding',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enterprise Tier
    |--------------------------------------------------------------------------
    */
    'enterprise' => [
        'name' => 'Enterprise',
        'description' => 'For organizations that need SSO, SLAs, and a dedicated support line.',
        'stripe_price_monthly' => env('STRIPE_PRICE_ENTERPRISE'),
        'stripe_price_annual' => env('STRIPE_PRICE_ENTERPRISE_ANNUAL'),
        'price_monthly' => env('PLAN_ENTERPRISE_PRICE_MONTHLY'),
        'price_annual' => env('PLAN_ENTERPRISE_PRICE_ANNUAL'),
        'per_seat' => true,
        'min_seats' => 10,
        'limits' => [
            'projects' => null, // unlimited
            'items_per_project' => null, // unlimited
            'api_tokens' => null, // unlimited
            'history_days' => null, // unlimited
            'seats' => null, // unlimited
        ],
        'features' => [
            'Everything in Team',
            'Unlimited seats',
            'Enterprise SSO (SAML 2.0)',
            'Custom integrations',
            'Dedicated support',
            'Uptime SLA with credits',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Past Due Grace Period
    |--------------------------------------------------------------------------
    |
    | Number of days to allow continued access after a subscription enters
    | past_due status. After this period, the user reverts to the free tier.
    |
    */
    'past_due_grace_days' => (int) env('PAST_DUE_GRACE_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Trial Configuration
    |--------------------------------------------------------------------------
    */
    'trial' => [
        'enabled' => env('TRIAL_ENABLED', true),
        'days' => env('TRIAL_DAYS', 14),
        'tier' => 'pro', // which tier to grant during trial
    ],
];
