<?php

namespace App\Services\Billing\Stats;

use Illuminate\Support\Facades\DB;

class ActivationRateCalculator
{
    /**
     * Rolling cohort activation rate: of users who signed up in the last $windowDays days,
     * what % completed onboarding? This avoids denominator inflation from pre-onboarding users.
     */
    public function calculate(int $windowDays = 90): float
    {
        $since = now()->subDays($windowDays);

        $cohortUsers = DB::table('users')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $since)
            ->count();

        if ($cohortUsers === 0) {
            return 0;
        }

        $activatedUsers = DB::table('user_settings')
            ->join('users', 'user_settings.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->where('users.created_at', '>=', $since)
            ->where('user_settings.key', 'onboarding_completed')
            ->distinct('user_settings.user_id')
            ->count('user_settings.user_id');

        return round(($activatedUsers / $cohortUsers) * 100, 1);
    }

    /**
     * All-time activation rate (legacy): users who completed onboarding / total signups.
     * Kept for transparency alongside the rolling cohort metric.
     */
    public function calculateAllTime(): float
    {
        $totalUsers = DB::table('users')->whereNull('deleted_at')->count();

        if ($totalUsers === 0) {
            return 0;
        }

        $activatedUsers = DB::table('user_settings')
            ->join('users', 'user_settings.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->where('user_settings.key', 'onboarding_completed')
            ->distinct('user_settings.user_id')
            ->count('user_settings.user_id');

        return round(($activatedUsers / $totalUsers) * 100, 1);
    }
}
