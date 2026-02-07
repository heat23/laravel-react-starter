<?php

namespace App\Services;

use App\Jobs\DispatchWebhookJob;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Str;

class WebhookService
{
    /**
     * Dispatch a webhook event to all matching active endpoints for a user.
     */
    public function dispatch(int $userId, string $eventType, array $payload): void
    {
        $endpoints = WebhookEndpoint::where('user_id', $userId)
            ->where('active', true)
            ->get()
            ->filter(fn (WebhookEndpoint $endpoint) => in_array($eventType, $endpoint->events));

        foreach ($endpoints as $endpoint) {
            $delivery = WebhookDelivery::create([
                'webhook_endpoint_id' => $endpoint->id,
                'uuid' => Str::uuid()->toString(),
                'event_type' => $eventType,
                'payload' => $payload,
                'status' => 'pending',
            ]);

            DispatchWebhookJob::dispatch($delivery->id);
        }
    }

    /**
     * Generate a cryptographically secure webhook secret.
     */
    public function generateSecret(): string
    {
        return 'whsec_'.Str::random(32);
    }

    /**
     * Sign a payload with HMAC-SHA256.
     */
    public function sign(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }
}
