<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\IncomingWebhookService;
use App\Webhooks\Contracts\WebhookProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncomingWebhookController extends Controller
{
    public function __construct(
        private IncomingWebhookService $incomingWebhookService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        /** @var WebhookProvider $provider */
        $provider = $request->attributes->get('webhook_provider');
        $rawPayload = $request->getContent();
        $event = $provider->parseEvent($rawPayload, $request->headers->all());

        $webhook = $this->incomingWebhookService->process($event);

        if (! $webhook) {
            return response()->json(['message' => 'Already processed.'], 200);
        }

        Log::channel('single')->info('Incoming webhook received', [
            'provider' => $event->provider,
            'external_id' => $event->externalId,
            'event_type' => $event->eventType,
        ]);

        return response()->json(['message' => 'Received.'], 200);
    }
}
