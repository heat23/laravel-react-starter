<?php

use App\Webhooks\Providers\CustomWebhookProvider;
use App\Webhooks\Providers\GithubWebhookProvider;

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
                'class' => GithubWebhookProvider::class,
                'secret' => env('GITHUB_WEBHOOK_SECRET'),
                'signature_header' => 'X-Hub-Signature-256',
                'algorithm' => 'sha256',
            ],
            'custom' => [
                'class' => CustomWebhookProvider::class,
                'secret' => env('CUSTOM_WEBHOOK_SECRET'),
                'signature_header' => 'X-Webhook-Signature',
                'algorithm' => 'sha256',
            ],
        ],
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
