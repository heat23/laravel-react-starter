<?php

namespace App\Services\Billing\Stats;

use Illuminate\Support\Facades\DB;

class TrialConversionCalculator
{
    public function calculate(): float
    {
        $totalTrialed = DB::table('subscriptions')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->count();

        if ($totalTrialed === 0) {
            return 0;
        }

        // Proxy for "ever converted": subscription was in trial AND ever moved to a paid status.
        // Counts subscriptions that converted even if they later churned — avoids survivor bias.
        // A subscription "ever converted" if it is now active, OR if it is canceled/past_due
        // and has an ends_at (meaning it was once billing and then canceled/expired).
        $converted = DB::table('subscriptions')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->where(function ($q) {
                $q->where('stripe_status', 'active')
                    ->orWhere(function ($q2) {
                        $q2->whereIn('stripe_status', ['canceled', 'past_due', 'incomplete_expired'])
                            ->whereNotNull('ends_at');
                    });
            })
            ->count();

        return round(($converted / $totalTrialed) * 100, 1);
    }
}
