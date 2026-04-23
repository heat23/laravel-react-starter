<?php

namespace App\Services\Billing\Stats;

use App\Enums\PlanTier;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;

class TierDistributionCalculator
{
    public function __construct(
        private BillingService $billingService,
    ) {}

    /** @return array<int, array{tier: string, count: int}> */
    public function calculate(): array
    {
        return DB::table('subscriptions')
            ->join('subscription_items', 'subscriptions.id', '=', 'subscription_items.subscription_id')
            ->whereNull('subscriptions.ends_at')
            ->whereIn('subscriptions.stripe_status', ['active', 'trialing'])
            ->select('subscription_items.stripe_price', DB::raw('COUNT(*) as count'))
            ->groupBy('subscription_items.stripe_price')
            ->get()
            ->map(fn ($row) => [
                'tier' => PlanTier::safeValue($this->billingService->resolveTierFromPrice($row->stripe_price)),
                'count' => (int) $row->count,
            ])
            ->groupBy('tier')
            ->map(fn ($group, $tier) => [
                'tier' => PlanTier::tryFrom($tier)?->label() ?? ucfirst($tier),
                'count' => $group->sum('count'),
            ])
            ->values()
            ->toArray();
    }
}
