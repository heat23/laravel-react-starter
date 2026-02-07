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
        'description' => 'For individuals getting started',
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
            'Manual operations',
            'Basic export (CSV)',
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
        'description' => 'For professionals',
        'stripe_price_monthly' => env('STRIPE_PRICE_PRO'),
        'stripe_price_annual' => env('STRIPE_PRICE_PRO_ANNUAL'),
        'price_monthly' => env('PLAN_PRO_PRICE_MONTHLY', 19),
        'price_annual' => env('PLAN_PRO_PRICE_ANNUAL', 190),
        'per_seat' => false,
        'limits' => [
            'projects' => null, // unlimited
            'items_per_project' => null, // unlimited
            'api_tokens' => env('PLAN_PRO_API_TOKENS', 10),
            'history_days' => env('PLAN_PRO_HISTORY_DAYS', 365),
            'seats' => 1,
        ],
        'features' => [
            'Unlimited projects',
            'Scheduled operations',
            'Advanced export (JSON, CSV)',
            'Email notifications',
            'Priority support',
            'API access',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Team Tier
    |--------------------------------------------------------------------------
    */
    'team' => [
        'name' => 'Team',
        'description' => 'For growing teams',
        'stripe_price_monthly' => env('STRIPE_PRICE_TEAM'),
        'stripe_price_annual' => env('STRIPE_PRICE_TEAM_ANNUAL'),
        'price_monthly' => env('PLAN_TEAM_PRICE_MONTHLY', 49),
        'price_annual' => env('PLAN_TEAM_PRICE_ANNUAL', 490),
        'per_seat' => true,
        'min_seats' => 3,
        'limits' => [
            'projects' => null, // unlimited
            'items_per_project' => null, // unlimited
            'api_tokens' => env('PLAN_TEAM_API_TOKENS', 25),
            'history_days' => null, // unlimited
            'seats' => env('PLAN_TEAM_MAX_SEATS', 50),
        ],
        'features' => [
            'Everything in Pro',
            'Team member management',
            'Per-seat billing',
            'Shared projects',
            'Audit log',
            'Priority email support',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enterprise Tier
    |--------------------------------------------------------------------------
    */
    'enterprise' => [
        'name' => 'Enterprise',
        'description' => 'For large organizations',
        'stripe_price_monthly' => env('STRIPE_PRICE_ENTERPRISE'),
        'stripe_price_annual' => env('STRIPE_PRICE_ENTERPRISE_ANNUAL'),
        'price_monthly' => env('PLAN_ENTERPRISE_PRICE_MONTHLY', 99),
        'price_annual' => env('PLAN_ENTERPRISE_PRICE_ANNUAL', 990),
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
            'SSO/SAML (when available)',
            'Custom integrations',
            'Dedicated support',
            'SLA guarantee',
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
        'enabled' => env('TRIAL_ENABLED', false),
        'days' => env('TRIAL_DAYS', 14),
        'tier' => 'pro', // which tier to grant during trial
    ],
];
