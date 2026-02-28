<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Webhook\CreateWebhookEndpointRequest;
use App\Http\Requests\Webhook\UpdateWebhookEndpointRequest;
use App\Services\CacheInvalidationManager;
use App\Services\PlanLimitService;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookEndpointController extends Controller
{
    public function __construct(
        private WebhookService $webhookService,
        private PlanLimitService $planLimitService,
        private CacheInvalidationManager $cacheManager,
    ) {
        abort_unless(feature_enabled('webhooks', auth()->user()), 404);
    }

    public function index(Request $request): JsonResponse
    {
        $endpoints = $request->user()->webhookEndpoints()
            ->withCount('deliveries')
            ->orderByDesc('created_at')
            ->take(config('pagination.api.webhook_endpoints', 50))
            ->get()
            ->map(fn ($endpoint) => [
                'id' => $endpoint->id,
                'url' => $endpoint->url,
                'events' => $endpoint->events,
                'description' => $endpoint->description,
                'active' => $endpoint->active,
                'deliveries_count' => $endpoint->deliveries_count,
                'created_at' => $endpoint->created_at->toISOString(),
            ]);

        return response()->json($endpoints);
    }

    public function store(CreateWebhookEndpointRequest $request): JsonResponse
    {
        $user = $request->user();
        $currentCount = $user->webhookEndpoints()->count();
        $plan = $this->planLimitService->getUserPlan($user);
        $maxKey = $plan === 'free' ? 'max_endpoints_free' : 'max_endpoints_pro';
        $limit = config("features.webhooks.{$maxKey}", 3);

        if ($currentCount >= $limit) {
            return response()->json([
                'message' => "You have reached the maximum of {$limit} webhook endpoints for your plan.",
            ], 422);
        }

        $endpoint = DB::transaction(fn () => $user->webhookEndpoints()->create([
            ...$request->validated(),
            'secret' => $this->webhookService->generateSecret(),
        ]));

        $this->invalidateAdminCaches();

        return response()->json([
            'id' => $endpoint->id,
            'secret' => $endpoint->secret,
        ], 201);
    }

    public function show(Request $request, int $endpointId): JsonResponse
    {
        $endpoint = $request->user()->webhookEndpoints()->findOrFail($endpointId);

        return response()->json([
            'id' => $endpoint->id,
            'url' => $endpoint->url,
            'events' => $endpoint->events,
            'description' => $endpoint->description,
            'active' => $endpoint->active,
            'secret' => $endpoint->secret,
            'created_at' => $endpoint->created_at->toISOString(),
        ]);
    }

    public function update(UpdateWebhookEndpointRequest $request, int $endpointId): JsonResponse
    {
        $endpoint = $request->user()->webhookEndpoints()->findOrFail($endpointId);

        DB::transaction(fn () => $endpoint->update($request->validated()));

        $this->invalidateAdminCaches();

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, int $endpointId): JsonResponse
    {
        $deleted = DB::transaction(fn () => $request->user()->webhookEndpoints()->where('id', $endpointId)->delete());

        if (! $deleted) {
            return response()->json(['message' => 'Endpoint not found.'], 404);
        }

        $this->invalidateAdminCaches();

        return response()->json(['success' => true]);
    }

    public function deliveries(Request $request, int $endpointId): JsonResponse
    {
        $endpoint = $request->user()->webhookEndpoints()->findOrFail($endpointId);

        $deliveries = $endpoint->deliveries()
            ->select(['id', 'webhook_endpoint_id', 'uuid', 'event_type', 'status', 'response_code', 'attempts', 'delivered_at', 'created_at'])
            ->orderByDesc('created_at')
            ->take(config('pagination.api.webhook_deliveries', 50))
            ->get()
            ->map(fn ($delivery) => [
                'id' => $delivery->id,
                'uuid' => $delivery->uuid,
                'event_type' => $delivery->event_type,
                'status' => $delivery->status,
                'response_code' => $delivery->response_code,
                'attempts' => $delivery->attempts,
                'delivered_at' => $delivery->delivered_at?->toISOString(),
                'created_at' => $delivery->created_at->toISOString(),
            ]);

        return response()->json($deliveries);
    }

    public function test(Request $request, int $endpointId): JsonResponse
    {
        $endpoint = $request->user()->webhookEndpoints()->findOrFail($endpointId);

        $this->webhookService->dispatchToEndpoint($endpoint, 'test.ping', [
            'message' => 'This is a test webhook delivery.',
            'timestamp' => now()->toISOString(),
        ]);

        return response()->json(['success' => true, 'message' => 'Test webhook queued.']);
    }

    private function invalidateAdminCaches(): void
    {
        $this->cacheManager->invalidateWebhooks();
    }
}
