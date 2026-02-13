<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminWebhooksController extends Controller
{
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
                ->limit(10)
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
}
