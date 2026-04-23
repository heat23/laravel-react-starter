<?php

namespace App\Services\Billing\Stats;

use Illuminate\Support\Facades\DB;

class TrialStatsCalculator
{
    /** @return array{active_trials: int, expiring_soon: int} */
    public function calculate(): array
    {
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
    }
}
