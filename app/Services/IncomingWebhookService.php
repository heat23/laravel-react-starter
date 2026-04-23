<?php

namespace App\Services;

use App\Models\IncomingWebhook;
use App\Webhooks\Dto\IncomingWebhookEvent;

class IncomingWebhookService
{
    /**
     * Process an incoming webhook, storing it for idempotency.
     *
     * @return IncomingWebhook|null Returns null if already processed (idempotent)
     */
    public function process(IncomingWebhookEvent $event): ?IncomingWebhook
    {
        if ($event->externalId) {
            $webhook = IncomingWebhook::firstOrCreate(
                ['provider' => $event->provider, 'external_id' => $event->externalId],
                [
                    'event_type' => $event->eventType,
                    'payload' => $event->payload,
                    'status' => 'received',
                ]
            );

            return $webhook->wasRecentlyCreated ? $webhook : null;
        }

        return IncomingWebhook::create([
            'provider' => $event->provider,
            'external_id' => $event->externalId,
            'event_type' => $event->eventType,
            'payload' => $event->payload,
            'status' => 'received',
        ]);
    }

    public function isProcessed(string $provider, string $externalId): bool
    {
        return IncomingWebhook::where('provider', $provider)
            ->where('external_id', $externalId)
            ->exists();
    }
}
