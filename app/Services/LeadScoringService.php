<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadScoringService
{
    private const MQL_THRESHOLD = 60;

    private const SQL_THRESHOLD = 80;

    private const PQL_LOOKBACK_DAYS = 30;

    public function __construct(
        private EngagementScoringService $engagementService,
        private CustomerHealthService $healthService,
    ) {}

    /**
     * Compute composite lead score: (engagement * 0.5) + (health * 0.3) + (pql_signal * 0.2)
     * pql_signal = 20 if user hit LIMIT_THRESHOLD_80 in last 30 days, else 0.
     */
    public function score(User $user): int
    {
        $engagementScore = $this->engagementService->score($user);
        $healthScore = $this->healthService->calculateHealthScore($user);
        $pqlSignal = $this->getPqlSignal($user->id);

        return min(100, (int) round(
            ($engagementScore * 0.5) + ($healthScore * 0.3) + ($pqlSignal * 0.2)
        ));
    }

    /**
     * Threshold constants for use by commands.
     */
    public function getMqlThreshold(): int
    {
        return self::MQL_THRESHOLD;
    }

    public function getSqlThreshold(): int
    {
        return self::SQL_THRESHOLD;
    }

    private function getPqlSignal(int $userId): int
    {
        $hasPqlEvent = DB::table('audit_logs')
            ->where('user_id', $userId)
            ->where('event', 'limit.threshold_80')
            ->where('created_at', '>=', now()->subDays(self::PQL_LOOKBACK_DAYS))
            ->exists();

        return $hasPqlEvent ? 20 : 0;
    }
}
