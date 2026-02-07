<?php

/**
 * Webhook Configuration
 *
 * Settings for incoming webhook processing and outgoing webhook delivery.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Incoming Webhooks
    |--------------------------------------------------------------------------
    |
    | Configure providers and their signature verification settings.
    |
    */
    'incoming' => [
        'providers' => [
            'github' => [
                'secret' => env('GITHUB_WEBHOOK_SECRET'),
                'signature_header' => 'X-Hub-Signature-256',
                'algorithm' => 'sha256',
            ],
            'stripe' => [
                'secret' => env('STRIPE_WEBHOOK_SECRET'),
                'signature_header' => 'Stripe-Signature',
                'algorithm' => 'sha256',
            ],
        ],
        'replay_tolerance' => 300, // seconds (5 minutes)
    ],

    /*
    |--------------------------------------------------------------------------
    | Outgoing Webhooks
    |--------------------------------------------------------------------------
    |
    | Configure delivery settings for outgoing webhooks.
    |
    */
    'outgoing' => [
        'timeout' => env('WEBHOOK_DELIVERY_TIMEOUT', 30),
        'retries' => env('WEBHOOK_DELIVERY_RETRIES', 3),
        'events' => [
            'user.created',
            'user.updated',
            'user.deleted',
        ],
    ],
];
