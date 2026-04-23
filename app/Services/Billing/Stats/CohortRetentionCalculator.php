<?php

namespace App\Services\Billing\Stats;

use Illuminate\Support\Facades\DB;

class CohortRetentionCalculator
{
    /**
     * Cohort retention: group users by signup week, show % active at week 1, 2, 4, 8.
     *
     * @return array<int, array{cohort: string, total: int, week_1: float|null, week_2: float|null, week_4: float|null, week_8: float|null}>
     */
    public function calculate(): array
    {
        $cohorts = [];
        $now = now();

        // Last 8 weeks of cohorts
        for ($i = 8; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $usersInCohort = DB::table('users')
                ->whereNull('deleted_at')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->pluck('id');

            $total = $usersInCohort->count();

            if ($total === 0) {
                continue;
            }

            $retention = [
                'cohort' => $weekStart->format('M d'),
                'total' => $total,
            ];

            foreach ([1, 2, 4, 8] as $week) {
                $checkDate = $weekStart->copy()->addWeeks($week);

                if ($checkDate->isAfter($now)) {
                    $retention["week_{$week}"] = null;
                } else {
                    // API-heavy users update last_active_at (not last_login_at) on each request.
                    // Use an OR to retain both session-based and API-based active users.
                    $activeCount = DB::table('users')
                        ->whereIn('id', $usersInCohort)
                        ->where(function ($q) use ($checkDate) {
                            $q->where('last_login_at', '>=', $checkDate)
                                ->orWhere('last_active_at', '>=', $checkDate);
                        })
                        ->count();

                    $retention["week_{$week}"] = round(($activeCount / $total) * 100, 1);
                }
            }

            $cohorts[] = $retention;
        }

        return $cohorts;
    }
}
