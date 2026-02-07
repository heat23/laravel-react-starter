<?php

namespace App\Services;

use App\Models\IncomingWebhook;

class IncomingWebhookService
{
    /**
     * Process an incoming webhook, storing it for idempotency.
     *
     * @return IncomingWebhook|null Returns null if already processed (idempotent)
     */
    public function process(string $provider, ?string $externalId, ?string $eventType, array $payload): ?IncomingWebhook
    {
        // Use atomic firstOrCreate when external_id is available to avoid TOCTOU race
        if ($externalId) {
            $webhook = IncomingWebhook::firstOrCreate(
                ['provider' => $provider, 'external_id' => $externalId],
                [
                    'event_type' => $eventType,
                    'payload' => $payload,
                    'status' => 'received',
                ]
            );

            // If it already existed, return null (idempotent)
            return $webhook->wasRecentlyCreated ? $webhook : null;
        }

        return IncomingWebhook::create([
            'provider' => $provider,
            'external_id' => $externalId,
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => 'received',
        ]);
    }

    /**
     * Check if a webhook has already been processed (idempotency check).
     */
    public function isProcessed(string $provider, string $externalId): bool
    {
        return IncomingWebhook::where('provider', $provider)
            ->where('external_id', $externalId)
            ->exists();
    }
}
