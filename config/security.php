<?php

return [
    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'report_only' => (bool) env('CSP_REPORT_ONLY', in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)),
        'report_uri' => env('CSP_REPORT_URI'),
    ],

    // HSTS preload opts the domain into browser preload lists (https://hstspreload.org).
    // Only enable after verifying ALL subdomains support HTTPS — this is irreversible
    // without a 1-year+ wait for removal from preload lists.
    'hsts_preload' => env('HSTS_PRELOAD', false),
];
