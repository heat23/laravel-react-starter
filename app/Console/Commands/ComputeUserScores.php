<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CustomerHealthService;
use App\Services\EngagementScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComputeUserScores extends Command
{
    protected $signature = 'users:compute-scores';

    protected $description = 'Compute and persist health and engagement scores for all active users';

    public function __construct(
        private CustomerHealthService $healthService,
        private EngagementScoringService $engagementService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $processed = 0;

        User::whereNull('deleted_at')
            ->withCount(['settings', 'tokens', 'webhookEndpoints'])
            ->chunkById(200, function ($users) use (&$processed) {
                $engagementScores = $this->engagementService->scoreBatch($users);
                $this->healthService->primeHealthScoreCaches($users);

                foreach ($users as $user) {
                    try {
                        $healthScore = $this->healthService->calculateHealthScore($user);
                        $engagementScore = $engagementScores[$user->id] ?? 0;

                        DB::table('users')->where('id', $user->id)->update([
                            'health_score' => $healthScore,
                            'engagement_score' => $engagementScore,
                            'scores_computed_at' => now(),
                        ]);

                        $processed++;
                    } catch (\Throwable $e) {
                        Log::warning('compute_user_scores_failed', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Computed scores for {$processed} users.");

        return self::SUCCESS;
    }
}
