<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use App\Helpers\QueryHelper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminBillingStatsService
{
    public function __construct(
        private BillingService $billingService,
    ) {}

    /**
     * @return array{active_subscriptions: int, trialing: int, past_due: int, canceled: int, total_ever: int, mrr: float, churn_rate: float, trial_conversion_rate: float}
     */
    public function getDashboardStats(): array
    {
        return Cache::remember(AdminCacheKey::BILLING_STATS->value, AdminCacheKey::DEFAULT_TTL, function () {
            return [
                'active_subscriptions' => DB::table('subscriptions')
                    ->where('stripe_status', 'active')
                    ->whereNull('ends_at')
                    ->count(),
                'trialing' => DB::table('subscriptions')
                    ->where('stripe_status', 'trialing')
                    ->count(),
                'past_due' => DB::table('subscriptions')
                    ->where('stripe_status', 'past_due')
                    ->count(),
                'canceled' => DB::table('subscriptions')
                    ->whereNotNull('ends_at')
                    ->count(),
                'total_ever' => DB::table('subscriptions')->count(),
                'mrr' => $this->calculateMrr(),
                'churn_rate' => $this->calculateChurnRate(),
                'trial_conversion_rate' => $this->calculateTrialConversion(),
            ];
        });
    }

    /** @return array<int, array{tier: string, count: int}> */
    public function getTierDistribution(): array
    {
        return Cache::remember(AdminCacheKey::BILLING_TIER_DIST->value, AdminCacheKey::DEFAULT_TTL, function () {
            return DB::table('subscriptions')
                ->join('subscription_items', 'subscriptions.id', '=', 'subscription_items.subscription_id')
                ->whereNull('subscriptions.ends_at')
                ->whereIn('subscriptions.stripe_status', ['active', 'trialing'])
                ->select('subscription_items.stripe_price', DB::raw('COUNT(*) as count'))
                ->groupBy('subscription_items.stripe_price')
                ->get()
                ->map(fn ($row) => [
                    'tier' => $this->billingService->resolveTierFromPrice($row->stripe_price),
                    'count' => (int) $row->count,
                ])
                ->groupBy('tier')
                ->map(fn ($group, $tier) => [
                    'tier' => ucfirst($tier),
                    'count' => $group->sum('count'),
                ])
                ->values()
                ->toArray();
        });
    }

    /** @return array<int, array{status: string, count: int}> */
    public function getStatusBreakdown(): array
    {
        return Cache::remember(AdminCacheKey::BILLING_STATUS->value, AdminCacheKey::DEFAULT_TTL, function () {
            return DB::table('subscriptions')
                ->select('stripe_status', DB::raw('COUNT(*) as count'))
                ->groupBy('stripe_status')
                ->get()
                ->map(fn ($row) => [
                    'status' => $row->stripe_status,
                    'count' => (int) $row->count,
                ])
                ->toArray();
        });
    }

    /** @return array<int, array{date: string, count: int}> */
    public function getGrowthChart(): array
    {
        return Cache::remember(AdminCacheKey::BILLING_GROWTH_CHART->value, AdminCacheKey::CHART_TTL, function () {
            return DB::table('subscriptions')
                ->select(QueryHelper::dateExpression('created_at'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($row) => ['date' => $row->date, 'count' => (int) $row->count])
                ->toArray();
        });
    }

    /** @return array{active_trials: int, expiring_soon: int} */
    public function getTrialStats(): array
    {
        return Cache::remember(AdminCacheKey::BILLING_TRIALS->value, AdminCacheKey::DEFAULT_TTL, function () {
            return [
                'active_trials' => DB::table('subscriptions')
                    ->where('stripe_status', 'trialing')
                    ->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now())
                    ->count(),
                'expiring_soon' => DB::table('subscriptions')
                    ->where('stripe_status', 'trialing')
                    ->whereNotNull('trial_ends_at')
                    ->whereBetween('trial_ends_at', [now(), now()->addDays(3)])
                    ->count(),
            ];
        });
    }

    /**
     * @param  array{search?: string, status?: string, tier?: string, sort?: string, dir?: string}  $validated
     */
    public function getFilteredSubscriptions(array $validated): LengthAwarePaginator
    {
        $firstItem = DB::table('subscription_items as si')
            ->select('si.subscription_id', 'si.stripe_price')
            ->whereRaw('si.id = (SELECT MIN(si2.id) FROM subscription_items AS si2 WHERE si2.subscription_id = si.subscription_id)');

        $query = DB::table('subscriptions')
            ->leftJoin('users', function ($join) {
                $join->on('subscriptions.user_id', '=', 'users.id')
                    ->whereNull('users.deleted_at');
            })
            ->leftJoinSub($firstItem, 'first_item', 'subscriptions.id', '=', 'first_item.subscription_id')
            ->select(
                'subscriptions.id',
                'subscriptions.user_id',
                'subscriptions.stripe_id',
                'subscriptions.stripe_status',
                'subscriptions.quantity',
                'subscriptions.trial_ends_at',
                'subscriptions.ends_at',
                'subscriptions.created_at',
                DB::raw("COALESCE(users.name, '[Deleted User]') as user_name"),
                DB::raw("COALESCE(users.email, '') as user_email"),
                'first_item.stripe_price as item_price',
            );

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('subscriptions.stripe_id', 'like', "%{$search}%");
            });
        }

        if (! empty($validated['status'])) {
            $query->where('subscriptions.stripe_status', $validated['status']);
        }

        if (! empty($validated['tier'])) {
            $tier = $validated['tier'];
            $monthlyPrice = config("plans.{$tier}.stripe_price_monthly");
            $annualPrice = config("plans.{$tier}.stripe_price_annual");
            $query->where(function ($q) use ($monthlyPrice, $annualPrice) {
                $q->where('first_item.stripe_price', $monthlyPrice);
                if ($annualPrice) {
                    $q->orWhere('first_item.stripe_price', $annualPrice);
                }
            });
        }

        $sortMap = [
            'created_at' => 'subscriptions.created_at',
            'stripe_status' => 'subscriptions.stripe_status',
            'quantity' => 'subscriptions.quantity',
            'user_name' => 'users.name',
        ];
        $sort = $sortMap[$validated['sort'] ?? 'created_at'] ?? 'subscriptions.created_at';
        $dir = $validated['dir'] ?? 'desc';
        $query->orderBy($sort, $dir);

        $billingService = $this->billingService;

        return $query->paginate(config('features.admin.pagination.default', 25))->through(fn ($row) => [
            'id' => $row->id,
            'user_id' => $row->user_id,
            'user_name' => $row->user_name,
            'user_email' => $row->user_email,
            'stripe_status' => $row->stripe_status,
            'tier' => $billingService->resolveTierFromPrice($row->item_price),
            'quantity' => $row->quantity,
            'trial_ends_at' => $row->trial_ends_at,
            'ends_at' => $row->ends_at,
            'created_at' => $row->created_at,
        ]);
    }

    private function calculateMrr(): float
    {
        $grouped = DB::table('subscriptions')
            ->join('subscription_items', 'subscriptions.id', '=', 'subscription_items.subscription_id')
            ->where('subscriptions.stripe_status', 'active')
            ->whereNull('subscriptions.ends_at')
            ->select('subscription_items.stripe_price', DB::raw('SUM(COALESCE(subscriptions.quantity, 1)) as total_quantity'))
            ->groupBy('subscription_items.stripe_price')
            ->get();

        $mrr = 0;
        foreach ($grouped as $row) {
            $tier = $this->billingService->resolveTierFromPrice($row->stripe_price);
            $monthlyPrice = (float) config("plans.{$tier}.price_monthly", 0);

            $annualPriceId = config("plans.{$tier}.stripe_price_annual");
            if ($row->stripe_price === $annualPriceId) {
                $monthlyPrice = (float) config("plans.{$tier}.price_annual", 0) / 12;
            }

            $mrr += $monthlyPrice * (int) $row->total_quantity;
        }

        return round($mrr, 2);
    }

    private function calculateChurnRate(): float
    {
        $thirtyDaysAgo = now()->subDays(30);

        $canceledInPeriod = DB::table('subscriptions')
            ->whereNotNull('ends_at')
            ->where('ends_at', '>=', $thirtyDaysAgo)
            ->count();

        $activeAtStart = DB::table('subscriptions')
            ->where('created_at', '<', $thirtyDaysAgo)
            ->where(function ($q) use ($thirtyDaysAgo) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', $thirtyDaysAgo);
            })
            ->count();

        if ($activeAtStart === 0) {
            return 0;
        }

        return round(($canceledInPeriod / $activeAtStart) * 100, 1);
    }

    private function calculateTrialConversion(): float
    {
        $totalTrialed = DB::table('subscriptions')
            ->whereNotNull('trial_ends_at')
            ->count();

        if ($totalTrialed === 0) {
            return 0;
        }

        $converted = DB::table('subscriptions')
            ->whereNotNull('trial_ends_at')
            ->where('stripe_status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->count();

        return round(($converted / $totalTrialed) * 100, 1);
    }
}
