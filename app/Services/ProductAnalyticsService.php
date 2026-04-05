<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use App\Helpers\QueryHelper;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductAnalyticsService
{
    /**
     * Signup trend: daily counts from audit_logs for auth.register events.
     *
     * @return array<int, array{date: string, count: int}>
     */
    public function getSignupTrend(int $days = 7): array
    {
        return Cache::remember(
            AdminCacheKey::PRODUCT_ANALYTICS_SIGNUP_TREND->value,
            AdminCacheKey::CHART_TTL,
            function () use ($days) {
                return DB::table('audit_logs')
                    ->select(QueryHelper::dateExpression('created_at'), DB::raw('COUNT(*) as count'))
                    ->where('event', 'auth.register')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->map(fn ($row) => ['date' => $row->date, 'count' => (int) $row->count])
                    ->toArray();
            }
        );
    }

    /**
     * Onboarding funnel: register → onboarding.started → onboarding.completed counts.
     *
     * @return array{registered: int, started: int, completed: int, start_rate: float, completion_rate: float}
     */
    public function getOnboardingFunnelConversion(): array
    {
        return Cache::remember(
            AdminCacheKey::PRODUCT_ANALYTICS_ONBOARDING_FUNNEL->value,
            AdminCacheKey::DEFAULT_TTL,
            function () {
                $since = now()->subDays(30);

                $registered = (int) DB::table('audit_logs')
                    ->where('event', 'auth.register')
                    ->where('created_at', '>=', $since)
                    ->count();

                $started = (int) DB::table('audit_logs')
                    ->where('event', 'onboarding.started')
                    ->where('created_at', '>=', $since)
                    ->count();

                $completed = (int) DB::table('audit_logs')
                    ->where('event', 'onboarding.completed')
                    ->where('created_at', '>=', $since)
                    ->count();

                return [
                    'registered' => $registered,
                    'started' => $started,
                    'completed' => $completed,
                    'start_rate' => $registered > 0 ? round($started / $registered * 100, 1) : 0.0,
                    'completion_rate' => $started > 0 ? round($completed / $started * 100, 1) : 0.0,
                ];
            }
        );
    }

    /**
     * Onboarding completion rate: % of users who have completed onboarding.
     * Reads from user_settings table where key = onboarding_completed.
     * Distinct from CustomerHealthService::getEmailVerificationRate() which measures
     * email verification within 7 days of signup.
     */
    public function getOnboardingCompletionRate(): array
    {
        return Cache::remember(
            AdminCacheKey::PRODUCT_ANALYTICS_ONBOARDING_COMPLETION->value,
            AdminCacheKey::DEFAULT_TTL,
            function () {
                $totalUsers = User::count();
                $activatedUsers = (int) DB::table('user_settings')
                    ->where('key', 'onboarding_completed')
                    ->count();

                return [
                    'total_users' => $totalUsers,
                    'activated_users' => $activatedUsers,
                    'activation_rate' => $totalUsers > 0 ? round($activatedUsers / $totalUsers * 100, 1) : 0.0,
                ];
            }
        );
    }

    /**
     * Feature adoption: top feature.used events from last 30 days, grouped by feature_name.
     *
     * @return array<int, array{feature_name: string, count: int}>
     */
    public function getFeatureAdoptionByWeek(): array
    {
        return Cache::remember(
            AdminCacheKey::PRODUCT_ANALYTICS_FEATURE_ADOPTION->value,
            AdminCacheKey::DEFAULT_TTL,
            function () {
                $driver = DB::getDriverName();

                if ($driver === 'sqlite') {
                    $nameExpr = "json_extract(metadata, '$.feature_name')";
                } else {
                    $nameExpr = "JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.feature_name'))";
                }

                return DB::table('audit_logs')
                    ->selectRaw("{$nameExpr} as feature_name, COUNT(*) as count")
                    ->where('event', 'feature.used')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->whereNotNull('metadata')
                    ->groupByRaw($nameExpr)
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get()
                    ->filter(fn ($row) => $row->feature_name !== null)
                    ->map(fn ($row) => ['feature_name' => $row->feature_name, 'count' => (int) $row->count])
                    ->values()
                    ->toArray();
            }
        );
    }

    /**
     * Key subscription events from last 30 days.
     *
     * @return array{created: int, canceled: int, resumed: int}
     */
    public function getSubscriptionEvents(): array
    {
        $since = now()->subDays(30);

        return [
            'created' => (int) DB::table('audit_logs')
                ->where('event', 'subscription.created')
                ->where('created_at', '>=', $since)
                ->count(),
            'canceled' => (int) DB::table('audit_logs')
                ->where('event', 'subscription.canceled')
                ->where('created_at', '>=', $since)
                ->count(),
            'resumed' => (int) DB::table('audit_logs')
                ->where('event', 'subscription.resumed')
                ->where('created_at', '>=', $since)
                ->count(),
        ];
    }
}
