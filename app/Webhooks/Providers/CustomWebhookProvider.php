<?php

namespace App\Webhooks\Providers;

use App\Webhooks\Contracts\WebhookProvider;
use App\Webhooks\Dto\IncomingWebhookEvent;
use Illuminate\Http\Request;

class CustomWebhookProvider implements WebhookProvider
{
    public function name(): string
    {
        return 'custom';
    }

    public function verify(Request $request, string $rawPayload): bool
    {
        $config = config('webhooks.incoming.providers.custom');
        $secret = $config['secret'] ?? null;

        if (! $secret) {
            return false;
        }

        $signatureHeader = $config['signature_header'] ?? 'X-Webhook-Signature';
        $algorithm = $config['algorithm'] ?? 'sha256';

        $received = $request->header($signatureHeader);

        if (! $received) {
            return false;
        }

        $expected = hash_hmac($algorithm, $rawPayload, $secret);

        return hash_equals($expected, $received);
    }

    public function parseEvent(string $rawPayload, array $headers): IncomingWebhookEvent
    {
        $payload = json_decode($rawPayload, true) ?? [];
        $externalId = $payload['id'] ?? null;
        $eventType = $payload['type'] ?? '';

        return new IncomingWebhookEvent(
            provider: $this->name(),
            eventType: $eventType,
            externalId: $externalId ? (string) $externalId : null,
            payload: $payload,
        );
    }
}
