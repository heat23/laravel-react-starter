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
        // Set to true before enabling FEATURE_BILLING if you want to soft-launch the pricing page
        // before payment processing goes live. Defaults false so billing works immediately on launch.
        'coming_soon' => env('PRO_TIER_COMING_SOON', false),
        // Trial config is in config/plans.php (plans.trial.days, plans.trial.enabled)
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
        // Defaults to true: auto-detection of providers by CLIENT_ID presence means no buttons
        // render unless GOOGLE_CLIENT_ID / GITHUB_CLIENT_ID are actually set — safe default.
        // Set FEATURE_SOCIAL_AUTH=false to suppress the feature-flag config entirely.
        'enabled' => env('FEATURE_SOCIAL_AUTH', true),
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
        // Token limits are defined in config/plans.php per tier (plans.*.limits.api_tokens).
        // Do not add per-tier limits here — single source of truth is config/plans.php.
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
        // Defaults to true so new users are guided through setup.
        // Set FEATURE_ONBOARDING=false for API-only or headless deployments
        // where no frontend onboarding wizard is needed.
        'enabled' => env('FEATURE_ONBOARDING', true),
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
    ],
];
