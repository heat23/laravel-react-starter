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

        if ($endpoints->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $endpoints->map(fn (WebhookEndpoint $endpoint) => [
            'webhook_endpoint_id' => $endpoint->id,
            'uuid' => Str::uuid()->toString(),
            'event_type' => $eventType,
            'payload' => json_encode($payload),
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        WebhookDelivery::insert($rows);

        $uuids = array_column($rows, 'uuid');
        $deliveryIds = WebhookDelivery::whereIn('uuid', $uuids)->pluck('id');

        foreach ($deliveryIds as $deliveryId) {
            DispatchWebhookJob::dispatch($deliveryId);
        }
    }

    /**
     * Dispatch a webhook event directly to a specific endpoint, bypassing event subscription filter.
     * Used for test deliveries.
     */
    public function dispatchToEndpoint(WebhookEndpoint $endpoint, string $eventType, array $payload): void
    {
        $delivery = WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'uuid' => Str::uuid()->toString(),
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => 'pending',
        ]);

        DispatchWebhookJob::dispatch($delivery->id);
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
