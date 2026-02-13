<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminNotificationsController extends Controller
{
    public function __invoke(): Response
    {
        $stats = Cache::remember(AdminCacheKey::NOTIFICATIONS_STATS->value, AdminCacheKey::DEFAULT_TTL, function () {
            $agg = DB::table('notifications')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) as read_count')
                ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as sent_last_7d', [now()->subDays(7)])
                ->first();

            $total = (int) $agg->total;
            $read = (int) $agg->read_count;

            return [
                'total_sent' => $total,
                'unread' => $total - $read,
                'read' => $read,
                'read_rate' => $total > 0 ? round(($read / $total) * 100, 1) : 0,
                'sent_last_7d' => (int) $agg->sent_last_7d,
                'by_type' => DB::table('notifications')
                    ->select('type', DB::raw('COUNT(*) as count'))
                    ->groupBy('type')
                    ->get()
                    ->map(fn ($row) => [
                        'type' => class_basename($row->type),
                        'count' => (int) $row->count,
                    ])
                    ->toArray(),
            ];
        });

        $volumeChart = Cache::remember(AdminCacheKey::NOTIFICATIONS_VOLUME->value, AdminCacheKey::CHART_TTL, function () {
            return DB::table('notifications')
                ->select(QueryHelper::dateExpression('created_at'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(14))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($row) => ['date' => $row->date, 'count' => (int) $row->count])
                ->toArray();
        });

        return Inertia::render('Admin/Notifications/Dashboard', [
            'stats' => $stats,
            'volume_chart' => $volumeChart,
        ]);
    }
}
