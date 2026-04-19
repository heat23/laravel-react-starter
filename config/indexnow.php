<?php

/**
 * IndexNow Configuration
 *
 * IndexNow (indexnow.org) is a protocol for notifying participating search
 * engines (Bing, Yandex, Seznam, Naver, Yep) of URL changes in real time.
 * A single submission to api.indexnow.org is syndicated to all participants.
 *
 * Feature gating lives in config/features.php ('indexnow.enabled'). This
 * file holds the service-level configuration (endpoint, key, host, queue).
 */

return [
    /*
    | The HTTP endpoint URLs are submitted to. Default is the unified
    | api.indexnow.org, which forwards to all participating engines.
    | Override with a single engine (e.g. https://www.bing.com/indexnow)
    | if you want to target one specifically.
    */
    'endpoint' => env('INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),

    /*
    | The host that owns submitted URLs. Defaults to the host parsed from
    | APP_URL. All URLs in a given submission must match this host.
    */
    'host' => env('INDEXNOW_HOST', parse_url((string) env('APP_URL'), PHP_URL_HOST)),

    /*
    | The IndexNow API key (8-128 chars, [a-zA-Z0-9-]). Generate one with
    | `php artisan indexnow:generate-key`. When unset, the service no-ops.
    */
    'key' => env('INDEXNOW_API_KEY'),

    /*
    | Optional explicit override for the keyLocation submitted to IndexNow.
    | When null, the service computes it as `{APP_URL}/{key}.txt`.
    */
    'key_location' => env('INDEXNOW_KEY_LOCATION'),

    /*
    | Request timeout in seconds for the outbound IndexNow HTTP call.
    */
    'timeout' => env('INDEXNOW_TIMEOUT', 15),

    /*
    | Queue connection/queue name for SubmitIndexNowUrlsJob.
    */
    'queue' => env('INDEXNOW_QUEUE', 'default'),
];
