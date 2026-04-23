<?php

namespace App\Webhooks\Providers;

use App\Webhooks\Contracts\WebhookProvider;
use App\Webhooks\Dto\IncomingWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GithubWebhookProvider implements WebhookProvider
{
    public function name(): string
    {
        return 'github';
    }

    public function verify(Request $request, string $rawPayload): bool
    {
        $secret = config('webhooks.incoming.providers.github.secret');

        if (! $secret) {
            return false;
        }

        $header = $request->header('X-Hub-Signature-256');

        if (! $header) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawPayload, $secret);
        $received = str_replace('sha256=', '', $header);

        return hash_equals($expected, $received);
    }

    public function parseEvent(string $rawPayload, array $headers): IncomingWebhookEvent
    {
        $payload = json_decode($rawPayload, true) ?? [];
        $eventType = $headers['x-github-event'][0] ?? '';

        if (isset($headers['x-github-delivery'][0])) {
            $externalId = $headers['x-github-delivery'][0];
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
