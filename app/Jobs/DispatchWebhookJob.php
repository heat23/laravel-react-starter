<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchWebhookJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(
        private readonly int $deliveryId,
    ) {}

    public function handle(WebhookService $webhookService): void
    {
        $delivery = WebhookDelivery::with('endpoint')->find($this->deliveryId);

        if (! $delivery || ! $delivery->endpoint || ! $delivery->endpoint->active) {
            return;
        }

        $endpoint = $delivery->endpoint;
        $payloadJson = json_encode($delivery->payload);
        $signature = $webhookService->sign($payloadJson, $endpoint->secret);
        $timestamp = time();

        $delivery->increment('attempts');

        try {
            $response = Http::timeout(config('webhooks.outgoing.timeout', 30))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Timestamp' => (string) $timestamp,
                    'X-Webhook-Id' => $delivery->uuid,
                    'X-Webhook-Event' => $delivery->event_type,
                    'User-Agent' => config('app.name').' Webhook/1.0',
                ])
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
            ]);

            $delivery->update([
                'status' => 'failed',
                'response_body' => substr($e->getMessage(), 0, 1000),
            ]);

            throw $e;
        }
    }
}
