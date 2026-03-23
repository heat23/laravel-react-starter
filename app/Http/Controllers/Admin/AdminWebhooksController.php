<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Enums\AnalyticsEvent;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Models\IncomingWebhook;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminWebhooksController extends Controller
{
    public function __construct(
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function __invoke(): Response
    {
        $stats = Cache::remember(AdminCacheKey::WEBHOOKS_STATS->value, AdminCacheKey::DEFAULT_TTL, function () {
            $endpoints = DB::table('webhook_endpoints')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN active = 1 AND deleted_at IS NULL THEN 1 ELSE 0 END) as active')
                ->first();

            $deliveries = DB::table('webhook_deliveries')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success")
                ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
                ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
                ->first();

            $totalDeliveries = (int) $deliveries->total;
            $failedDeliveries = (int) $deliveries->failed;

            return [
                'total_endpoints' => (int) $endpoints->total,
                'active_endpoints' => (int) $endpoints->active,
                'total_deliveries' => $totalDeliveries,
                'successful_deliveries' => (int) $deliveries->success,
                'failed_deliveries' => $failedDeliveries,
                'pending_deliveries' => (int) $deliveries->pending,
                'failure_rate' => $totalDeliveries > 0 ? round(($failedDeliveries / $totalDeliveries) * 100, 1) : 0,
                'total_incoming' => DB::table('incoming_webhooks')->count(),
                'incoming_by_provider' => DB::table('incoming_webhooks')
                    ->select('provider', DB::raw('COUNT(*) as count'))
                    ->groupBy('provider')
                    ->pluck('count', 'provider')
                    ->toArray(),
            ];
        });

        $deliveryChart = Cache::remember(AdminCacheKey::WEBHOOKS_DELIVERY_CHART->value, AdminCacheKey::CHART_TTL, function () {
            $rows = DB::table('webhook_deliveries')
                ->select(
                    QueryHelper::dateExpression('created_at'),
                    'status',
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(14))
                ->groupBy('date', 'status')
                ->orderBy('date')
                ->get();

            $grouped = [];
            foreach ($rows as $row) {
                $grouped[$row->date] ??= ['date' => $row->date, 'success' => 0, 'failed' => 0];
                if (in_array($row->status, ['success', 'failed'])) {
                    $grouped[$row->date][$row->status] = (int) $row->count;
                }
            }

            return array_values($grouped);
        });

        $recentFailures = Cache::remember(AdminCacheKey::WEBHOOKS_RECENT_FAILURES->value, AdminCacheKey::DEFAULT_TTL, function () {
            return DB::table('webhook_deliveries')
                ->join('webhook_endpoints', 'webhook_deliveries.webhook_endpoint_id', '=', 'webhook_endpoints.id')
                ->where('webhook_deliveries.status', 'failed')
                ->orderByDesc('webhook_deliveries.created_at')
                ->limit(config('pagination.admin.recent_events', 10))
                ->select(
                    'webhook_deliveries.id',
                    'webhook_deliveries.event_type',
                    'webhook_deliveries.response_code',
                    'webhook_deliveries.attempts',
                    'webhook_deliveries.created_at',
                    'webhook_endpoints.url as endpoint_url',
                )
                ->get()
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'event_type' => $row->event_type,
                    'endpoint_url' => $row->endpoint_url,
                    'response_code' => $row->response_code,
                    'attempts' => $row->attempts,
                    'created_at' => $row->created_at,
                ])
                ->toArray();
        });

        return Inertia::render('Admin/Webhooks/Dashboard', [
            'stats' => $stats,
            'delivery_chart' => $deliveryChart,
            'recent_failures' => $recentFailures,
        ]);
    }

    public function endpoints(): Response
    {
        $endpoints = WebhookEndpoint::withTrashed()
            ->with(['user' => fn ($q) => $q->withTrashed()->select('id', 'name', 'email')])
            ->withCount('deliveries')
            ->orderByRaw('deleted_at IS NOT NULL ASC')
            ->orderByDesc('created_at')
            ->paginate(config('pagination.admin.users', 25))
            ->through(function ($ep) {
                /** @var WebhookEndpoint $ep */
                $user = $ep->user instanceof User ? $ep->user : null;

                return [
                    'id' => $ep->id,
                    'user_id' => $ep->user_id,
                    'user_name' => $user !== null ? $user->name : '[Deleted User]',
                    'user_email' => $user !== null ? $user->email : '',
                    'url' => $ep->url,
                    'description' => $ep->description,
                    'active' => $ep->active,
                    'events' => $ep->events,
                    'deliveries_count' => $ep->deliveries_count,
                    'deleted_at' => $ep->deleted_at?->toISOString(),
                    'created_at' => $ep->created_at?->toISOString(),
                ];
            });

        return Inertia::render('Admin/Webhooks/Endpoints', [
            'endpoints' => $endpoints,
        ]);
    }

    public function incomingWebhooks(Request $request): Response
    {
        $provider = $request->string('provider')->toString() ?: null;
        $status = $request->string('status')->toString() ?: null;
        $eventType = $request->string('event_type')->toString() ?: null;

        $validStatuses = ['received', 'processing', 'processed', 'failed'];
        if ($status !== null && ! in_array($status, $validStatuses, true)) {
            $status = null;
        }

        $query = IncomingWebhook::latest();

        if ($provider !== null) {
            $query->where('provider', $provider);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($eventType !== null) {
            $escaped = QueryHelper::escapeLike($eventType);
            $query->whereRaw("event_type LIKE ? ESCAPE '|'", ["%{$escaped}%"]);
        }

        $webhooks = $query
            ->paginate(config('pagination.admin.users', 25))
            ->withQueryString()
            ->through(fn (IncomingWebhook $w) => [
                'id' => $w->id,
                'provider' => $w->provider,
                'external_id' => $w->external_id,
                'event_type' => $w->event_type,
                'status' => $w->status,
                'payload' => $w->payload,
                'created_at' => $w->created_at?->toISOString(),
            ]);

        $providers = IncomingWebhook::distinct()->orderBy('provider')->pluck('provider')->toArray();

        return Inertia::render('Admin/Webhooks/IncomingWebhooks', [
            'webhooks' => $webhooks,
            'providers' => $providers,
            'filters' => array_filter([
                'provider' => $provider,
                'status' => $status,
                'event_type' => $eventType,
            ]),
        ]);
    }

    public function restoreEndpoint(int $id, Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $endpoint = WebhookEndpoint::withTrashed()->findOrFail($id);

        abort_unless($endpoint->trashed(), 422, 'Endpoint is not deleted.');

        $endpoint->restore();

        $this->auditService->log(AnalyticsEvent::ADMIN_WEBHOOK_ENDPOINT_RESTORED, [
            'endpoint_id' => $endpoint->id,
            'url' => $endpoint->url,
        ]);

        $this->cacheManager->invalidateWebhooks();

        return redirect()->route('admin.webhooks.endpoints')
            ->with('success', 'Webhook endpoint restored.');
    }
}
