<?php

namespace App\Services\Billing\Stats;

use Illuminate\Support\Facades\DB;

class SignupConversionCalculator
{
    /**
     * Signup-to-paid conversion: users with active subscriptions / total signups.
     */
    public function calculate(): float
    {
        $totalUsers = DB::table('users')->whereNull('deleted_at')->count();

        if ($totalUsers === 0) {
            return 0;
        }

        $paidUsers = DB::table('subscriptions')
            ->where('stripe_status', 'active')
            ->whereNull('ends_at')
            ->distinct('user_id')
            ->count('user_id');

        return round(($paidUsers / $totalUsers) * 100, 1);
    }

    /**
     * Cohort conversion (rolling 30d): of users who signed up in the last 30 days,
     * what % have an active paid subscription?
     */
    public function calculateCohorted(): float
    {
        $thirtyDaysAgo = now()->subDays(30);

        $cohortUsers = DB::table('users')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        if ($cohortUsers === 0) {
            return 0;
        }

        $converted = DB::table('users')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('subscriptions')
                    ->whereColumn('subscriptions.user_id', 'users.id')
                    ->where('stripe_status', 'active')
                    ->whereNull('ends_at');
            })
            ->count();

        return round(($converted / $cohortUsers) * 100, 1);
    }
}
