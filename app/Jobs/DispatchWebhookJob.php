<?php

namespace App\Jobs;

use App\Enums\AuditEvent;
use App\Models\WebhookDelivery;
use App\Services\AuditService;
use App\Services\WebhookService;
use App\Webhooks\UrlPolicy;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(
        private readonly int $deliveryId,
    ) {}

    public function handle(WebhookService $webhookService, AuditService $auditService): void
    {
        $delivery = WebhookDelivery::with('endpoint')->find($this->deliveryId);

        if (! $delivery || ! $delivery->endpoint || ! $delivery->endpoint->active) {
            return;
        }

        $endpoint = $delivery->endpoint;

        // Runtime SSRF guard — resolves DNS at dispatch time to defeat DNS rebinding.
        $policy = new UrlPolicy;
        $blockReason = $policy->check($endpoint->url);

        if ($blockReason !== null) {
            $delivery->update([
                'status' => 'failed',
                'response_body' => "BLOCKED: {$blockReason}",
            ]);

            Log::channel('single')->warning('Webhook delivery blocked by URL policy', [
                'delivery_id' => $delivery->id,
                'url' => $endpoint->url,
                'reason' => $blockReason,
            ]);

            $auditService->log(AuditEvent::ADMIN_WEBHOOK_DELIVERY_BLOCKED, [
                'delivery_id' => $delivery->id,
                'endpoint_id' => $endpoint->id,
                'url' => $endpoint->url,
                'reason' => $blockReason,
            ]);

            return;
        }

        $payloadJson = json_encode($delivery->payload);
        $signature = $webhookService->sign($payloadJson, $endpoint->secret);
        $timestamp = time();

        // DNS pin: use the first IP resolved by the policy check to prevent rebinding
        // between the DNS resolution above and the actual TCP connect below.
        $resolvedIp = $policy->resolvedIps()[0] ?? null;
        $parsed = parse_url($endpoint->url);
        $host = $parsed['host'] ?? '';
        $scheme = strtolower($parsed['scheme'] ?? 'https');
        $port = $parsed['port'] ?? ($scheme === 'https' ? 443 : 80);

        $delivery->increment('attempts');

        try {
            $httpClient = Http::timeout(config('webhooks.outgoing.timeout', 30))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Timestamp' => (string) $timestamp,
                    'X-Webhook-Id' => $delivery->uuid,
                    'X-Webhook-Event' => $delivery->event_type,
                    'User-Agent' => config('app.name').' Webhook/1.0',
                ]);

            // Pin DNS resolution to the address we already validated so a race
            // between our SSRF check and the HTTP connect cannot be exploited.
            if ($resolvedIp !== null && $host !== '') {
                $httpClient = $httpClient->withOptions([
                    'curl' => [
                        CURLOPT_RESOLVE => ["{$host}:{$port}:{$resolvedIp}"],
                    ],
                ]);
            }

            $response = $httpClient
                ->withBody($payloadJson, 'application/json')
                ->post($endpoint->url);

            $delivery->update([
                'status' => $response->successful() ? 'success' : 'failed',
                'response_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 1000),
                'delivered_at' => $response->successful() ? now() : null,
            ]);

            if (! $response->successful()) {
                $this->fail(new \RuntimeException(
                    "Webhook delivery failed with status {$response->status()}"
                ));
            }
        } catch (\Throwable $e) {
            Log::channel('single')->warning('Webhook delivery failed', [
                'delivery_id' => $delivery->id,
                'endpoint_url' => $endpoint->url,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
            ]);

            // Only mark as failed on final attempt; intermediate attempts will be retried
            if ($this->attempts() >= $this->tries) {
                $delivery->update([
                    'status' => 'failed',
                    'response_body' => substr($e->getMessage(), 0, 1000),
                ]);
            }

            throw $e;
        }
    }
}
