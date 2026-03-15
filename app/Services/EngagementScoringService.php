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
        $score = 0;

        $score += $this->loginRecencyScore($user);
        $score += $this->featureAdoptionScore($user);
        $score += $this->onboardingScore($user);
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

    private function featureAdoptionScore(User $user): int
    {
        $score = 0;

        // Settings customized
        $settingsCount = DB::table('user_settings')
            ->where('user_id', $user->id)
            ->count();
        if ($settingsCount > 0) {
            $score += 8;
        }

        // API tokens created
        $tokenCount = DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', User::class)
            ->count();
        if ($tokenCount > 0) {
            $score += 9;
        }

        // Webhook endpoints (if table exists)
        try {
            $webhookCount = DB::table('webhook_endpoints')
                ->where('user_id', $user->id)
                ->count();
            if ($webhookCount > 0) {
                $score += 8;
            }
        } catch (\Illuminate\Database\QueryException) {
            // Table doesn't exist
        }

        return min(25, $score);
    }

    private function onboardingScore(User $user): int
    {
        $completed = DB::table('user_settings')
            ->where('user_id', $user->id)
            ->where('key', 'onboarding_completed')
            ->exists();

        return $completed ? 25 : 0;
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
