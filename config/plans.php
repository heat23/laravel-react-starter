<?php

/**
 * Subscription Plans Configuration
 *
 * Defines limits and features for each subscription tier.
 * Only used when billing feature is enabled.
 */

return [
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
        'limits' => [
            'projects' => env('PLAN_FREE_PROJECTS', 3),
            'items_per_project' => env('PLAN_FREE_ITEMS', 50),
            'api_tokens' => env('PLAN_FREE_API_TOKENS', 1),
            'history_days' => env('PLAN_FREE_HISTORY_DAYS', 30),
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
        'description' => 'For professionals and small teams',
        'stripe_price_monthly' => env('STRIPE_PRICE_PRO'),
        'stripe_price_annual' => env('STRIPE_PRICE_PRO_ANNUAL'),
        'price_monthly' => env('PLAN_PRO_PRICE_MONTHLY', 19),
        'price_annual' => env('PLAN_PRO_PRICE_ANNUAL', 190),
        'limits' => [
            'projects' => null, // unlimited
            'items_per_project' => null, // unlimited
            'api_tokens' => env('PLAN_PRO_API_TOKENS', 10),
            'history_days' => env('PLAN_PRO_HISTORY_DAYS', 365),
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
    | Trial Configuration
    |--------------------------------------------------------------------------
    */
    'trial' => [
        'enabled' => env('TRIAL_ENABLED', false),
        'days' => env('TRIAL_DAYS', 14),
        'tier' => 'pro', // which tier to grant during trial
    ],
];
