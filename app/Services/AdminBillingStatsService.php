<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use App\Services\Billing\Stats\ChurnBreakdownCalculator;
use App\Services\Billing\Stats\CohortRetentionCalculator;
use App\Services\Billing\Stats\DashboardStatsCalculator;
use App\Services\Billing\Stats\GrowthChartCalculator;
use App\Services\Billing\Stats\StatusBreakdownCalculator;
use App\Services\Billing\Stats\SubscriptionQueryBuilder;
use App\Services\Billing\Stats\TierDistributionCalculator;
use App\Services\Billing\Stats\TrialStatsCalculator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class AdminBillingStatsService
{
    public function __construct(
        private DashboardStatsCalculator $dashboardStats,
        private TierDistributionCalculator $tierDistribution,
        private StatusBreakdownCalculator $statusBreakdown,
        private GrowthChartCalculator $growthChart,
        private TrialStatsCalculator $trialStats,
        private ChurnBreakdownCalculator $churnBreakdown,
        private CohortRetentionCalculator $cohortRetention,
        private SubscriptionQueryBuilder $subscriptionQuery,
    ) {}

    /**
     * @return array{active_subscriptions: int, trialing: int, past_due: int, canceled: int, scheduled_cancellations: int, total_ever: int, mrr: float, churn_rate: float, trial_conversion_rate: float, activation_rate: float, activation_rate_all_time: float, signup_to_paid_conversion: float, cohort_conversion_30d: float}
     */
    public function getDashboardStats(): array
    {
        return Cache::remember(
            AdminCacheKey::BILLING_STATS->value,
            AdminCacheKey::DEFAULT_TTL,
            fn () => $this->dashboardStats->calculate()
        );
    }

    /** @return array<int, array{tier: string, count: int}> */
    public function getTierDistribution(): array
    {
        return Cache::remember(
            AdminCacheKey::BILLING_TIER_DIST->value,
            AdminCacheKey::DEFAULT_TTL,
            fn () => $this->tierDistribution->calculate()
        );
    }

    /** @return array<int, array{status: string, count: int}> */
    public function getStatusBreakdown(): array
    {
        return Cache::remember(
            AdminCacheKey::BILLING_STATUS->value,
            AdminCacheKey::DEFAULT_TTL,
            fn () => $this->statusBreakdown->calculate()
        );
    }

    /** @return array<int, array{date: string, count: int}> */
    public function getGrowthChart(): array
    {
        return Cache::remember(
            AdminCacheKey::BILLING_GROWTH_CHART->value,
            AdminCacheKey::CHART_TTL,
            fn () => $this->growthChart->calculate()
        );
    }

    /** @return array{active_trials: int, expiring_soon: int} */
    public function getTrialStats(): array
    {
        return Cache::remember(
            AdminCacheKey::BILLING_TRIALS->value,
            AdminCacheKey::DEFAULT_TTL,
            fn () => $this->trialStats->calculate()
        );
    }

    /** @return array{voluntary: int, involuntary: int} */
    public function getChurnBreakdown(): array
    {
        return Cache::remember(
            AdminCacheKey::BILLING_STATS->value.'_churn_breakdown',
            AdminCacheKey::DEFAULT_TTL,
            fn () => $this->churnBreakdown->calculate()
        );
    }

    /**
     * @return array<int, array{cohort: string, total: int, week_1: float|null, week_2: float|null, week_4: float|null, week_8: float|null}>
     */
    public function getCohortRetention(): array
    {
        return Cache::remember(
            AdminCacheKey::BILLING_COHORT_RETENTION->value,
            AdminCacheKey::CHART_TTL,
            fn () => $this->cohortRetention->calculate()
        );
    }

    /**
     * @param  array{search?: string, status?: string, tier?: string, sort?: string, dir?: string}  $validated
     */
    public function getFilteredSubscriptions(array $validated): LengthAwarePaginator
    {
        return $this->subscriptionQuery->paginated($validated);
    }

    /**
     * @param  array{search?: string, status?: string, tier?: string, sort?: string, dir?: string}  $validated
     */
    public function buildSubscriptionQuery(array $validated): Builder
    {
        return $this->subscriptionQuery->build($validated);
    }
}
