<?php

namespace App\Console\Commands;

use App\Events\LeadQualifiedEvent;
use App\Models\User;
use App\Services\LeadScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QualifyLeads extends Command
{
    protected $signature = 'emails:qualify-leads';

    protected $description = 'Compute composite lead scores and dispatch qualification events';

    public function __construct(
        private LeadScoringService $leadScoringService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $mqlThreshold = $this->leadScoringService->getMqlThreshold();
        $sqlThreshold = $this->leadScoringService->getSqlThreshold();
        $processed = 0;

        User::whereNull('deleted_at')
            ->whereNotNull('email_verified_at')
            ->withCount(['settings', 'tokens', 'webhookEndpoints'])
            ->chunkById(200, function ($users) use ($mqlThreshold, $sqlThreshold, &$processed) {
                foreach ($users as $user) {
                    try {
                        $score = $this->leadScoringService->score($user);
                        $previous = (int) ($user->lead_score ?? 0);

                        DB::table('users')->where('id', $user->id)->update([
                            'lead_score' => $score,
                        ]);

                        // Dispatch qualification events when crossing thresholds upward
                        if ($score >= $sqlThreshold && $previous < $sqlThreshold) {
                            LeadQualifiedEvent::dispatch($user, $score, 'sql');
                        } elseif ($score >= $mqlThreshold && $previous < $mqlThreshold) {
                            LeadQualifiedEvent::dispatch($user, $score, 'mql');

                            // Mark lead_qualified_at on first MQL qualification (atomic to prevent concurrent writes)
                            DB::table('users')
                                ->where('id', $user->id)
                                ->whereNull('lead_qualified_at')
                                ->update(['lead_qualified_at' => now()]);
                        }

                        $processed++;
                    } catch (\Throwable $e) {
                        Log::warning('qualify_leads_failed', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Processed {$processed} leads.");

        return self::SUCCESS;
    }
}
