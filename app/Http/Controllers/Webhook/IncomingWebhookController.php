<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\IncomingWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncomingWebhookController extends Controller
{
    public function __construct(
        private IncomingWebhookService $incomingWebhookService
    ) {}

    public function handle(Request $request, string $provider): JsonResponse
    {
        $payload = $request->all();
        $externalId = $this->extractExternalId($request, $provider);
        $eventType = $this->extractEventType($request, $provider);

        $webhook = $this->incomingWebhookService->process(
            $provider,
            $externalId,
            $eventType,
            $payload
        );

        if (! $webhook) {
            return response()->json(['message' => 'Already processed.'], 200);
        }

        Log::channel('single')->info('Incoming webhook received', [
            'provider' => $provider,
            'external_id' => $externalId,
            'event_type' => $eventType,
        ]);

        return response()->json(['message' => 'Received.'], 200);
    }

    private function extractExternalId(Request $request, string $provider): ?string
    {
        return match ($provider) {
            'github' => $request->header('X-GitHub-Delivery'),
            'stripe' => $request->input('id'),
            default => null,
        };
    }

    private function extractEventType(Request $request, string $provider): ?string
    {
        return match ($provider) {
            'github' => $request->header('X-GitHub-Event'),
            'stripe' => $request->input('type'),
            default => $request->input('type'),
        };
    }
}
