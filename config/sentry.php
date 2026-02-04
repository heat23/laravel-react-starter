<?php

/**
 * Sentry Configuration
 *
 * This configuration is used when sentry/sentry-laravel is installed.
 * Install with: composer require sentry/sentry-laravel
 *
 * @see https://docs.sentry.io/platforms/php/guides/laravel/
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Sentry DSN
    |--------------------------------------------------------------------------
    |
    | The DSN tells the SDK where to send the events. If this value is not
    | provided, the SDK will just not send any events.
    |
    */

    'dsn' => env('SENTRY_LARAVEL_DSN'),

    /*
    |--------------------------------------------------------------------------
    | Release
    |--------------------------------------------------------------------------
    |
    | Associate errors with specific releases. Uses git commit hash by default.
    |
    */

    'release' => env('SENTRY_RELEASE'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Automatically set to your APP_ENV value if not explicitly set.
    |
    */

    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs
    |--------------------------------------------------------------------------
    |
    | Control which breadcrumb types are recorded. Breadcrumbs provide a trail
    | of events leading up to an error.
    |
    */

    'breadcrumbs' => [
        'logs' => true,
        'sql_queries' => true,
        'sql_bindings' => true,
        'queue_info' => true,
        'command_info' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    |
    | Set trace_sample_rate to 1.0 to capture 100% of transactions for
    | performance monitoring. We recommend adjusting this value in production.
    |
    */

    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0),

    /*
    |--------------------------------------------------------------------------
    | Profiling
    |--------------------------------------------------------------------------
    |
    | Enable profiling for performance insights. Requires traces to be enabled.
    |
    */

    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),

    /*
    |--------------------------------------------------------------------------
    | Send Default PII
    |--------------------------------------------------------------------------
    |
    | If this flag is enabled, certain personally identifiable information (PII)
    | is added by active integrations. By default, no such data is sent.
    |
    */

    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    /*
    |--------------------------------------------------------------------------
    | Controller Base Namespace
    |--------------------------------------------------------------------------
    |
    | The base namespace for your controllers. Used for better error grouping.
    |
    */

    'controllers_base_namespace' => env('SENTRY_CONTROLLERS_BASE_NAMESPACE', 'App\\Http\\Controllers'),

];
