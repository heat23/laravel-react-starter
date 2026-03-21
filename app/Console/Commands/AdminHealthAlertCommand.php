<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AdminHealthAlertNotification;
use App\Services\CustomerHealthService;
use App\Services\HealthCheckService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminHealthAlertCommand extends Command
{
    protected $signature = 'admin:health-alert';

    protected $description = 'Check system health and alert admins if thresholds are breached';

    public function handle(HealthCheckService $healthCheck, CustomerHealthService $customerHealth): int
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
