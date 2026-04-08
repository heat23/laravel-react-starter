<?php

namespace App\Console\Commands;

use App\Enums\AdminCacheKey;
use App\Models\User;
use App\Notifications\AdminHealthAlertNotification;
use App\Services\AdminBillingStatsService;
use App\Services\CustomerHealthService;
use App\Services\HealthCheckService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminHealthAlertCommand extends Command
{
    protected $signature = 'admin:health-alert';

    protected $description = 'Check system health and alert admins if thresholds are breached';

    public function handle(HealthCheckService $healthCheck, CustomerHealthService $customerHealth, AdminBillingStatsService $billingStats): int
    {
        $alerts = [];

        // Check failed jobs
        $failedJobsThreshold = (int) config('health.alert_thresholds.failed_jobs', 10);
        $failedJobsCount = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->count()
            : 0;

        if ($failedJobsCount > $failedJobsThreshold) {
            $alerts['failed_jobs'] = [
                'count' => $failedJobsCount,
                'threshold' => $failedJobsThreshold,
                'message' => "Failed jobs ({$failedJobsCount}) exceed threshold ({$failedJobsThreshold})",
            ];
        }

        // Check overall health
        $health = $healthCheck->runAllChecks();
        if ($health['status'] === 'unhealthy') {
            $failedChecks = collect($health['checks'])
                ->filter(fn ($check) => $check['status'] === 'error')
                ->keys()
                ->implode(', ');

            $alerts['health_status'] = [
                'status' => $health['status'],
                'failed_checks' => $failedChecks,
                'message' => "Health status: {$health['status']} (failed: {$failedChecks})",
            ];
        }

        // Check webhook failure rate
        if (Schema::hasTable('webhook_deliveries')) {
            $webhookThreshold = (int) config('health.alert_thresholds.webhook_failure_rate', 25);
            $last24h = now()->subDay();

            $totalDeliveries = DB::table('webhook_deliveries')
                ->where('created_at', '>=', $last24h)
                ->count();

            if ($totalDeliveries > 0) {
                $failedDeliveries = DB::table('webhook_deliveries')
                    ->where('created_at', '>=', $last24h)
                    ->where('status', 'failed')
                    ->count();

                $failureRate = round(($failedDeliveries / $totalDeliveries) * 100, 1);

                if ($failureRate > $webhookThreshold) {
                    $alerts['webhook_failure_rate'] = [
                        'rate' => $failureRate,
                        'threshold' => $webhookThreshold,
                        'message' => "Webhook failure rate ({$failureRate}%) exceeds threshold ({$webhookThreshold}%)",
                    ];
                }
            }
        }

        // Check D7 retention rate
        $d7Threshold = (int) config('health.alert_thresholds.d7_retention', 35);
        $d7Rate = $customerHealth->getD7RetentionRate();
        if ($d7Rate > 0 && $d7Rate < $d7Threshold) {
            $alerts['d7_retention'] = [
                'rate' => $d7Rate,
                'threshold' => $d7Threshold,
                'message' => "D7 retention ({$d7Rate}%) is below threshold ({$d7Threshold}%)",
            ];
        }

        // Check D30 retention rate
        $d30Threshold = (int) config('health.alert_thresholds.d30_retention', 15);
        $d30Rate = $customerHealth->getD30RetentionRate();
        if ($d30Rate > 0 && $d30Rate < $d30Threshold) {
            $alerts['d30_retention'] = [
                'rate' => $d30Rate,
                'threshold' => $d30Threshold,
                'message' => "D30 retention ({$d30Rate}%) is below threshold ({$d30Threshold}%)",
            ];
        }

        // Check analytics thresholds: churn rate and trial conversion (billing-gated)
        if (Schema::hasTable('subscriptions')) {
            $dashboardStats = $billingStats->getDashboardStats();
            $churnRate = (float) $dashboardStats['churn_rate'];
            $churnWarning = (float) config('analytics-thresholds.churn_rate.warning', 10);
            $churnCritical = (float) config('analytics-thresholds.churn_rate.critical', 20);

            if ($churnRate > 0 && $churnRate >= $churnCritical) {
                $alerts['churn_rate'] = [
                    'rate' => $churnRate,
                    'threshold' => $churnCritical,
                    'severity' => 'critical',
                    'message' => "Churn rate ({$churnRate}%) is at or above critical threshold ({$churnCritical}%)",
                ];
            } elseif ($churnRate > 0 && $churnRate >= $churnWarning) {
                $alerts['churn_rate'] = [
                    'rate' => $churnRate,
                    'threshold' => $churnWarning,
                    'severity' => 'warning',
                    'message' => "Churn rate ({$churnRate}%) is at or above warning threshold ({$churnWarning}%)",
                ];
            }

            $trialConversionWarning = (float) config('analytics-thresholds.trial_conversion.warning_below', 20);
            $trialConversionCritical = (float) config('analytics-thresholds.trial_conversion.critical_below', 10);
            $trialConversionRate = $customerHealth->getTrialConversionRate();

            if ($trialConversionRate > 0 && $trialConversionRate <= $trialConversionCritical) {
                $alerts['trial_conversion'] = [
                    'rate' => $trialConversionRate,
                    'threshold' => $trialConversionCritical,
                    'severity' => 'critical',
                    'message' => "Trial conversion rate ({$trialConversionRate}%) is below critical threshold ({$trialConversionCritical}%)",
                ];
            } elseif ($trialConversionRate > 0 && $trialConversionRate < $trialConversionWarning) {
                $alerts['trial_conversion'] = [
                    'rate' => $trialConversionRate,
                    'threshold' => $trialConversionWarning,
                    'severity' => 'warning',
                    'message' => "Trial conversion rate ({$trialConversionRate}%) is below warning threshold ({$trialConversionWarning}%)",
                ];
            }

            // Check activation rate threshold
            $activationRate = (float) $dashboardStats['activation_rate'];
            $activationWarning = (float) config('analytics-thresholds.activation_rate.warning_below', 40);
            $activationCritical = (float) config('analytics-thresholds.activation_rate.critical_below', 20);

            if ($activationRate > 0 && $activationRate <= $activationCritical) {
                $alerts['activation_rate'] = [
                    'rate' => $activationRate,
                    'threshold' => $activationCritical,
                    'severity' => 'critical',
                    'message' => "Activation rate ({$activationRate}%) is below critical threshold ({$activationCritical}%)",
                ];
            } elseif ($activationRate > 0 && $activationRate < $activationWarning) {
                $alerts['activation_rate'] = [
                    'rate' => $activationRate,
                    'threshold' => $activationWarning,
                    'severity' => 'warning',
                    'message' => "Activation rate ({$activationRate}%) is below warning threshold ({$activationWarning}%)",
                ];
            }

            // Check MRR drop against previous run snapshot
            $currentMrr = (float) $dashboardStats['mrr'];
            $previousMrr = Cache::get(AdminCacheKey::METRICS_MRR_SNAPSHOT->value);

            if ($previousMrr !== null) {
                $previousMrr = (float) $previousMrr;
                if ($previousMrr > 0 && $currentMrr < $previousMrr) {
                    $mrrDropPercent = round((($previousMrr - $currentMrr) / $previousMrr) * 100, 1);
                    $mrrDropWarning = (float) config('analytics-thresholds.mrr_drop_percent.warning', 10);
                    $mrrDropCritical = (float) config('analytics-thresholds.mrr_drop_percent.critical', 25);

                    if ($mrrDropPercent >= $mrrDropCritical) {
                        $alerts['mrr_drop'] = [
                            'drop_percent' => $mrrDropPercent,
                            'previous_mrr' => $previousMrr,
                            'current_mrr' => $currentMrr,
                            'threshold' => $mrrDropCritical,
                            'severity' => 'critical',
                            'message' => "MRR dropped {$mrrDropPercent}% (from \${$previousMrr} to \${$currentMrr}), exceeding critical threshold ({$mrrDropCritical}%)",
                        ];
                    } elseif ($mrrDropPercent >= $mrrDropWarning) {
                        $alerts['mrr_drop'] = [
                            'drop_percent' => $mrrDropPercent,
                            'previous_mrr' => $previousMrr,
                            'current_mrr' => $currentMrr,
                            'threshold' => $mrrDropWarning,
                            'severity' => 'warning',
                            'message' => "MRR dropped {$mrrDropPercent}% (from \${$previousMrr} to \${$currentMrr}), exceeding warning threshold ({$mrrDropWarning}%)",
                        ];
                    }
                }
            }

            // Update MRR snapshot for next run comparison (7-day TTL — safe to miss 1 run).
            // Guard: skip when MRR is $0 and there are no subscriptions at all — that signals
            // a data gap (empty environment / failed stats fetch), not genuine $0 revenue.
            // Prevents overwriting a valid snapshot with a misleading $0 that would cause a
            // false critical alert on the subsequent run.
            $totalSubscriptions = (int) $dashboardStats['total_ever'];
            if ($currentMrr > 0 || $totalSubscriptions > 0) {
                Cache::put(AdminCacheKey::METRICS_MRR_SNAPSHOT->value, $currentMrr, now()->addDays(7));
            }
        }

        if (empty($alerts)) {
            $this->info('No alerts — all thresholds within limits.');

            return self::SUCCESS;
        }

        // Send notification to all admin users
        $admins = User::where('is_admin', true)->get();

        foreach ($admins as $admin) {
            try {
                $admin->notify(new AdminHealthAlertNotification($alerts));
            } catch (\Exception $e) {
                Log::error('Failed to send health alert to admin', [
                    'admin_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->warn('Alert sent to '.count($admins).' admin(s): '.implode(', ', array_keys($alerts)));

        return self::SUCCESS;
    }
}
