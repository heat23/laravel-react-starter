<?php

namespace App\Webhooks\Providers;

use App\Webhooks\Contracts\WebhookProvider;
use App\Webhooks\Dto\IncomingWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $eventType = $payload['type'] ?? '';

        if (isset($payload['id'])) {
            $externalId = (string) $payload['id'];
        } else {
            $externalId = hash('sha256', $rawPayload);
            Log::warning('Provider emitted synthetic webhook id', ['provider' => $this->name()]);
        }

        return new IncomingWebhookEvent(
            provider: $this->name(),
            eventType: $eventType,
            externalId: $externalId,
            payload: $payload,
        );
    }
}
