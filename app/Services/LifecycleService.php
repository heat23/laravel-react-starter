<?php

namespace App\Services;

use App\Enums\AdminCacheKey;
use App\Enums\LifecycleStage;
use App\Models\User;
use App\Models\UserStageHistory;
use App\Notifications\WinBackNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LifecycleService
{
    /**
     * Transition a user to a new lifecycle stage.
     * Records the transition in audit_logs and user_stage_history.
     */
    public function transition(User $user, LifecycleStage $to, string $reason): void
    {
        $from = $user->lifecycle_stage ? LifecycleStage::tryFrom($user->lifecycle_stage) : null;

        // No-op if already in this stage
        if ($from?->value === $to->value) {
            return;
        }

        // Compute days in previous stage
        $daysInPreviousStage = null;
        if ($from !== null) {
            $lastTransition = UserStageHistory::where('user_id', $user->id)
                ->where('to_stage', $from->value)
                ->latest('created_at')
                ->first();
            if ($lastTransition) {
                $daysInPreviousStage = (int) $lastTransition->created_at->diffInDays(now());
            }
        }

        DB::transaction(function () use ($user, $from, $to, $reason, $daysInPreviousStage) {
            // Update the user's lifecycle stage.
            // saveQuietly() suppresses Eloquent model events intentionally — any observers
            // that dispatch jobs, send notifications, or make HTTP calls must NOT run inside
            // this transaction boundary, as their side-effects cannot be rolled back and may
            // extend the transaction lock. Post-transition side-effects (win-back notification,
            // cache invalidation, audit log) are dispatched after the transaction closes below.
            $user->lifecycle_stage = $to->value;
            $user->saveQuietly();

            // Record in history table
            UserStageHistory::create([
                'user_id' => $user->id,
                'from_stage' => $from?->value,
                'to_stage' => $to->value,
                'reason' => $reason,
                'metadata' => array_filter([
                    'days_in_previous_stage' => $daysInPreviousStage,
                ]),
                'created_at' => now(),
            ]);
        });

        // Audit log outside transaction (fire-and-forget via queued job)
        try {
            app(AuditService::class)->log('lifecycle.transition', [
                'user_id' => $user->id,
                'from' => $from?->value,
                'to' => $to->value,
                'reason' => $reason,
                'days_in_previous_stage' => $daysInPreviousStage,
            ]);
        } catch (\Throwable $e) {
            Log::warning('lifecycle_audit_failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Invalidate stage funnel cache
        Cache::forget(AdminCacheKey::STAGE_FUNNEL->value);

        // Side-effects on specific transitions
        if ($to === LifecycleStage::CHURNED) {
            // Queue win-back notification with 24h delay
            try {
                $user->notify((new WinBackNotification(1))->delay(now()->addHours(24)));
            } catch (\Throwable $e) {
                Log::warning('lifecycle_winback_dispatch_failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get median days between stage pairs for last 90 days.
     *
     * Uses true median (not mean) so outliers like a user stuck in trial for
     * 200 days do not skew the result. Median computed in PHP after fetching
     * raw per-transition durations to remain cross-DB compatible.
     *
     * @return array<string, float>
     */
    public function getStageVelocity(): array
    {
        $cacheKey = AdminCacheKey::STAGE_VELOCITY->value;

        return Cache::remember($cacheKey, AdminCacheKey::CHART_TTL, function () {
            if (! DB::getSchemaBuilder()->hasTable('user_stage_history')) {
                return [];
            }

            $driver = DB::getDriverName();
            $diffExpr = $driver === 'sqlite'
                ? 'CAST((julianday(curr.created_at) - julianday(prev.created_at)) AS INTEGER)'
                : 'TIMESTAMPDIFF(DAY, prev.created_at, curr.created_at)';

            $rows = DB::select("
                SELECT curr.from_stage, curr.to_stage, {$diffExpr} as days
                FROM user_stage_history curr
                JOIN user_stage_history prev ON prev.user_id = curr.user_id
                    AND prev.to_stage = curr.from_stage
                WHERE curr.created_at >= ?
            ", [now()->subDays(90)]);

            /** @var array<string, list<float>> $grouped */
            $grouped = [];
            foreach ($rows as $row) {
                $key = "{$row->from_stage}_to_{$row->to_stage}";
                $grouped[$key][] = (float) $row->days;
            }

            $result = [];
            foreach ($grouped as $key => $values) {
                sort($values);
                $count = count($values);
                $mid = (int) floor($count / 2);
                $median = $count % 2 === 0
                    ? ($values[$mid - 1] + $values[$mid]) / 2.0
                    : $values[$mid];
                $result[$key] = round($median, 1);
            }

            return $result;
        });
    }
}
