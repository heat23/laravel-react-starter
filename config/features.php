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
        //
        // Tax compliance (PRODUCTION REQUIRED in many jurisdictions):
        // Enabling Stripe Tax is a two-key gate — you must set BOTH env vars to true:
        //   FEATURE_BILLING_TAX=true            # switch the feature on
        //   BILLING_TAX_CONFIRM_COMPLIANT=true  # explicit attestation that you've registered
        //                                       # for Stripe Tax in all applicable jurisdictions
        //                                       # (stripe.com/tax) and verified tax codes per plan.
        // Both keys required so that a single "remember to turn on tax" checklist item can't
        // ship uncollected tax liability to production. BillingService reads this composite flag
        // and passes automatic_tax: { enabled: true } to all new subscriptions only if it is true.
        // See app/Services/BillingService.php and the Stripe Tax documentation.
        'tax_enabled' => env('FEATURE_BILLING_TAX', false)
            && env('BILLING_TAX_CONFIRM_COMPLIANT', false),
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
        // Defaults to false so a fresh install doesn't claim to support social login.
        // Enable by setting FEATURE_SOCIAL_AUTH=true AND configuring provider credentials
        // (GOOGLE_CLIENT_ID and/or GITHUB_CLIENT_ID). Provider buttons render only for
        // providers whose CLIENT_ID is set; the feature flag is the master switch.
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
        // Defaults to true because this starter is used by sole operators who need admin
        // access to their own app's dashboard (user management, health, audit logs, feature
        // flag overrides). The admin middleware still requires a user with is_admin=true,
        // so enabling this flag without an admin user grants nothing. Set FEATURE_ADMIN=false
        // only if you are shipping a public/anonymous app with no internal operations UI.
        'enabled' => env('FEATURE_ADMIN', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | IndexNow (Search Engine Instant Indexing)
    |--------------------------------------------------------------------------
    |
    | When enabled, exposes the IndexNow service for pinging search engines
    | (Bing, Yandex, Seznam, Naver, Yep) when URLs change. Requires an API
    | key generated via `php artisan indexnow:generate-key`.
    |
    */
    'indexnow' => [
        'enabled' => env('FEATURE_INDEXNOW', false),
        // Defaults to true when FEATURE_INDEXNOW is on so turning the parent flag on
        // isn't a silent no-op. Set INDEXNOW_AUTO_PING_SITEMAP=false explicitly to opt
        // out of the 24h-cached sitemap-wide ping and only ping manually-submitted URLs.
        'auto_ping_sitemap' => env('INDEXNOW_AUTO_PING_SITEMAP', env('FEATURE_INDEXNOW', false)),
        'max_urls_per_submission' => 10000,
        'debounce_minutes' => env('INDEXNOW_DEBOUNCE_MINUTES', 10),
    ],
];
