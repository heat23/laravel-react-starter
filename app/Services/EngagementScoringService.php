<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class EngagementScoringService
{
    /**
     * Calculate an engagement score (0-100) for a user.
     *
     * Factors:
     * - Login recency (0-25): last login within 1d=25, 7d=15, 30d=5, else=0
     * - Feature adoption (0-25): based on settings customized, tokens created, webhooks
     * - Onboarding completion (0-25): completed onboarding = 25
     * - Account age adjustment (0-25): older active accounts score higher
     */
    public function score(User $user): int
    {
        return $this->computeScore(
            user: $user,
            settingsCount: (int) DB::table('user_settings')->where('user_id', $user->id)->count(),
            tokenCount: (int) DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->where('tokenable_id', $user->id)->count(),
            webhookCount: $this->countWebhooks($user->id),
            onboardingComplete: DB::table('user_settings')
                ->where('user_id', $user->id)
                ->where('key', 'onboarding_completed')
                ->where('value', '1')
                ->exists(),
        );
    }

    /**
     * Compute engagement scores for a collection of users in 4 queries total.
     *
     * @return array<int, int> Keyed by user ID
     */
    public function scoreBatch(\Illuminate\Support\Collection $users): array
    {
        $userIds = $users->pluck('id');

        $settingsCounts = DB::table('user_settings')
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('count(*) as cnt'))
            ->pluck('cnt', 'user_id');

        $tokenCounts = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->whereIn('tokenable_id', $userIds)
            ->groupBy('tokenable_id')
            ->select('tokenable_id', DB::raw('count(*) as cnt'))
            ->pluck('cnt', 'tokenable_id');

        $webhookCounts = collect();
        try {
            $webhookCounts = DB::table('webhook_endpoints')
                ->whereIn('user_id', $userIds)
                ->groupBy('user_id')
                ->select('user_id', DB::raw('count(*) as cnt'))
                ->pluck('cnt', 'user_id');
        } catch (\Illuminate\Database\QueryException) {
            // Table doesn't exist
        }

        $onboardingCompleted = DB::table('user_settings')
            ->whereIn('user_id', $userIds)
            ->where('key', 'onboarding_completed')
            ->where('value', '1')
            ->pluck('user_id')
            ->flip();

        $scores = [];
        foreach ($users as $user) {
            $id = $user->id;
            $scores[$id] = $this->computeScore(
                user: $user,
                settingsCount: (int) ($settingsCounts[$id] ?? 0),
                tokenCount: (int) ($tokenCounts[$id] ?? 0),
                webhookCount: (int) ($webhookCounts[$id] ?? 0),
                onboardingComplete: isset($onboardingCompleted[$id]),
            );
        }

        return $scores;
    }

    private function computeScore(
        User $user,
        int $settingsCount,
        int $tokenCount,
        int $webhookCount,
        bool $onboardingComplete,
    ): int {
        $score = 0;

        $score += $this->loginRecencyScore($user);
        $score += $this->featureAdoptionScoreFromCounts($settingsCount, $tokenCount, $webhookCount);
        $score += $onboardingComplete ? 25 : 0;
        $score += $this->accountMaturityScore($user);

        return min(100, max(0, $score));
    }

    private function loginRecencyScore(User $user): int
    {
        if (! $user->last_login_at) {
            return 0;
        }

        $daysSinceLogin = (int) now()->diffInDays($user->last_login_at);

        if ($daysSinceLogin <= 1) {
            return 25;
        }
        if ($daysSinceLogin <= 7) {
            return 15;
        }
        if ($daysSinceLogin <= 30) {
            return 5;
        }

        return 0;
    }

    private function featureAdoptionScoreFromCounts(int $settingsCount, int $tokenCount, int $webhookCount): int
    {
        $score = 0;

        if ($settingsCount > 0) {
            $score += 8;
        }

        if ($tokenCount > 0) {
            $score += 9;
        }

        if ($webhookCount > 0) {
            $score += 8;
        }

        return min(25, $score);
    }

    private function countWebhooks(int $userId): int
    {
        try {
            return (int) DB::table('webhook_endpoints')
                ->where('user_id', $userId)
                ->count();
        } catch (\Illuminate\Database\QueryException) {
            return 0;
        }
    }

    private function accountMaturityScore(User $user): int
    {
        if (! $user->created_at) {
            return 0;
        }

        $daysOld = (int) now()->diffInDays($user->created_at);

        // Only valuable if also active (has logged in recently)
        if (! $user->last_login_at) {
            return 0;
        }

        if ($daysOld >= 90) {
            return 25;
        }
        if ($daysOld >= 30) {
            return 15;
        }
        if ($daysOld >= 7) {
            return 8;
        }

        return 3;
    }
}
