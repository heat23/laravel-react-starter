<?php

return [
    'csp' => [
        'enabled' => env('CSP_ENABLED', false),
        'report_only' => env('CSP_REPORT_ONLY', true),
        'report_uri' => env('CSP_REPORT_URI'),
    ],
];
