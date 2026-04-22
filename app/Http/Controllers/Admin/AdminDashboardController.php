<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminCacheKey;
use App\Enums\LifecycleStage;
use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(): Response
    {
        $stats = Cache::remember(AdminCacheKey::DASHBOARD_STATS->value, AdminCacheKey::DEFAULT_TTL, function () {
            $stats = [
                'total_users' => User::count(),
                'total_deactivated' => User::onlyTrashed()->count(),
                'new_users_7d' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'new_users_30d' => User::where('created_at', '>=', now()->subDays(30))->count(),
                'admin_count' => User::where('is_admin', true)->count(),
            ];

            if (config('features.billing.enabled')) {
                $stats['active_subscriptions'] = DB::table('subscriptions')
                    ->where('stripe_status', 'active')
                    ->whereNull('ends_at')
                    ->count();
            }

            $stats['cached_at'] = now()->toISOString();

            return $stats;
        });

        $signupChart = Cache::remember(AdminCacheKey::DASHBOARD_SIGNUP_CHART->value, AdminCacheKey::CHART_TTL, function () {
            return User::select(QueryHelper::dateExpression('created_at'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($row) => ['date' => $row->date, 'count' => (int) $row->count])
                ->toArray();
        });

        $recentActivity = AuditLog::with('user')
            ->latest()
            ->limit(config('pagination.admin.recent_activity', 15))
            ->get()
            ->map(fn (AuditLog $log) => $log->toSummaryArray());

        $stageFunnel = Cache::remember(AdminCacheKey::STAGE_FUNNEL->value, AdminCacheKey::DEFAULT_TTL, function () {
            $stages = LifecycleStage::funnelOrder();
            $counts = User::whereNull('deleted_at')
                ->selectRaw('lifecycle_stage, COUNT(*) as count')
                ->groupBy('lifecycle_stage')
                ->pluck('count', 'lifecycle_stage');

            return collect($stages)->map(fn ($stage) => [
                'stage' => $stage->value,
                'label' => $stage->label(),
                'count' => (int) ($counts[$stage->value] ?? 0),
            ])->values()->all();
        });

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'signup_chart' => $signupChart,
            'recent_activity' => $recentActivity,
            'stage_funnel' => $stageFunnel,
        ]);
    }
}
