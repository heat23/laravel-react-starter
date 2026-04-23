<?php

namespace App\Services\Billing\Stats;

use Illuminate\Support\Facades\DB;

class ChurnRateCalculator
{
    public function calculate(): float
    {
        $thirtyDaysAgo = now()->subDays(30);

        $canceledInPeriod = DB::table('subscriptions')
            ->whereNotNull('ends_at')
            ->where('ends_at', '>=', $thirtyDaysAgo)
            ->where(function ($q) {
                $q->where('stripe_status', 'canceled')
                    ->orWhere('ends_at', '<', now());
            })
            ->count();

        // Only paying subscribers (active or past_due) count in the denominator.
        // Trialing subscriptions are excluded to avoid artificially deflating churn rate.
        $activeAtStart = DB::table('subscriptions')
            ->where('created_at', '<', $thirtyDaysAgo)
            ->whereIn('stripe_status', ['active', 'past_due'])
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
}
