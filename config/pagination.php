<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pagination & Query Limit Defaults
    |--------------------------------------------------------------------------
    |
    | Centralized configuration for all pagination sizes and query limits.
    | Controllers should reference these values instead of hardcoding numbers.
    |
    */

    'default' => 25,

    'admin' => [
        'users' => 25,
        'audit_logs' => 50,
        'recent_activity' => 15,
        'recent_events' => 10,
        'subscription_logs' => 20,
    ],

    'api' => [
        'tokens' => 50,
        'webhook_endpoints' => 50,
        'webhook_deliveries' => 50,
        'notifications' => 20,
    ],

    'billing' => [
        'invoices' => 12,
    ],

    'export' => [
        'max_rows' => 10000,
    ],
];
