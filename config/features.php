<?php

/**
 * Feature Flags Configuration
 *
 * Enable or disable optional modules via environment variables.
 * Each feature can be toggled without code changes.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Billing / Subscription Features
    |--------------------------------------------------------------------------
    |
    | When enabled, adds Stripe billing integration via Laravel Cashier.
    | Requires: laravel/cashier package and STRIPE_* env variables.
    |
    */
    'billing' => [
        'enabled' => env('FEATURE_BILLING', false),
        'trial_days' => env('TRIAL_DAYS', 14),
        'trial_enabled' => env('TRIAL_ENABLED', false),
        'coming_soon' => env('PRO_TIER_COMING_SOON', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Authentication
    |--------------------------------------------------------------------------
    |
    | When enabled, adds OAuth login via Google and/or GitHub.
    | Requires: laravel/socialite package and OAuth credentials.
    |
    */
    'social_auth' => [
        'enabled' => env('FEATURE_SOCIAL_AUTH', false),
        'providers' => array_filter([
            env('GOOGLE_CLIENT_ID') ? 'google' : null,
            env('GITHUB_CLIENT_ID') ? 'github' : null,
        ]),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Verification
    |--------------------------------------------------------------------------
    |
    | When enabled, requires users to verify their email before accessing
    | protected areas of the application.
    |
    */
    'email_verification' => [
        'enabled' => env('FEATURE_EMAIL_VERIFICATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Tokens (Sanctum)
    |--------------------------------------------------------------------------
    |
    | When enabled, users can create personal access tokens for API access.
    | Uses Laravel Sanctum for token management.
    |
    */
    'api_tokens' => [
        'enabled' => env('FEATURE_API_TOKENS', true),
        'max_tokens_free' => env('API_TOKENS_MAX_FREE', 1),
        'max_tokens_pro' => env('API_TOKENS_MAX_PRO', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Settings
    |--------------------------------------------------------------------------
    |
    | When enabled, users can customize their preferences (theme, timezone, etc.)
    | Stored in user_settings table.
    |
    */
    'user_settings' => [
        'enabled' => env('FEATURE_USER_SETTINGS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | When enabled, adds in-app notification system with bell icon dropdown,
    | unread count badge, and mark-as-read functionality.
    |
    */
    'notifications' => [
        'enabled' => env('FEATURE_NOTIFICATIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Onboarding
    |--------------------------------------------------------------------------
    |
    | When enabled, new users are guided through a welcome wizard before
    | accessing the dashboard. Stores completion timestamp in user_settings.
    |
    */
    'onboarding' => [
        'enabled' => env('FEATURE_ONBOARDING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Documentation (Scribe)
    |--------------------------------------------------------------------------
    |
    | When enabled, interactive API documentation is available at /docs.
    | Requires: knuckleswtf/scribe dev dependency.
    |
    */
    'api_docs' => [
        'enabled' => env('FEATURE_API_DOCS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    |
    | When enabled, users can set up TOTP-based 2FA for their accounts.
    | Requires: laragear/two-factor package.
    |
    */
    'two_factor' => [
        'enabled' => env('FEATURE_TWO_FACTOR', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks (Incoming & Outgoing)
    |--------------------------------------------------------------------------
    |
    | When enabled, supports incoming webhook processing (GitHub, Stripe)
    | and outgoing webhook delivery to user-configured endpoints.
    |
    */
    'webhooks' => [
        'enabled' => env('FEATURE_WEBHOOKS', false),
        'max_endpoints_free' => env('WEBHOOK_ENDPOINTS_MAX_FREE', 3),
        'max_endpoints_pro' => env('WEBHOOK_ENDPOINTS_MAX_PRO', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel
    |--------------------------------------------------------------------------
    |
    | When enabled, adds an admin panel at /admin with user management,
    | health monitoring, audit logs, config viewer, and system info.
    |
    */
    'admin' => [
        'enabled' => env('FEATURE_ADMIN', false),
        'pagination' => [
            'default' => 25,
            'audit_logs' => 50,
        ],
    ],
];
