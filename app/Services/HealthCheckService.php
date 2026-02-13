<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class HealthCheckService
{
    /** @var array<string, callable> */
    protected array $customChecks = [];

    /**
     * Register a custom health check. Downstream projects call this to add
     * their own checks (Redis, external APIs, etc.) without modifying the service.
     */
    public function registerCheck(string $name, callable $check): void
    {
        $this->customChecks[$name] = $check;
    }

    /**
     * Run all health checks and return aggregated results.
     * Results are cached for 5 seconds to prevent database strain from rapid refreshes.
     *
     * @return array{status: string, checks: array, timestamp: string}
     */
    public function runAllChecks(): array
    {
        return Cache::remember('health_checks', 5, function () {
            $checks = [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'queue' => $this->checkQueue(),
                'disk' => $this->checkDisk(),
            ];

            foreach ($this->customChecks as $name => $check) {
                $checks[$name] = $this->timedCheck($check);
            }

            $statuses = array_column($checks, 'status');
            $overallStatus = match (true) {
                in_array('error', $statuses) => 'unhealthy',
                in_array('warning', $statuses) => 'degraded',
                default => 'healthy',
            };

            return [
                'status' => $overallStatus,
                'checks' => $checks,
                'timestamp' => now()->toISOString(),
            ];
        });
    }

    public function checkDatabase(): array
    {
        return $this->timedCheck(function () {
            DB::select('SELECT 1');

            return ['status' => 'ok', 'message' => 'Connection successful'];
        });
    }

    public function checkCache(): array
    {
        return $this->timedCheck(function () {
            $key = 'health_check_'.uniqid();
            $value = 'ok';

            Cache::put($key, $value, 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);

            if ($retrieved !== $value) {
                return ['status' => 'error', 'message' => 'Cache read/write mismatch'];
            }

            return ['status' => 'ok', 'message' => 'Read/write successful'];
        });
    }

    public function checkQueue(): array
    {
        return $this->timedCheck(function () {
            $size = Queue::size();
            $threshold = (int) config('health.queue_warning_threshold', 1000);

            if ($size > $threshold) {
                return ['status' => 'warning', 'message' => 'Queue backlog detected'];
            }

            return ['status' => 'ok', 'message' => 'Queue nominal'];
        });
    }

    public function checkDisk(): array
    {
        return $this->timedCheck(function () {
            $total = disk_total_space(base_path());
            $free = disk_free_space(base_path());

            if ($total === false || $free === false || $total === 0) {
                return ['status' => 'error', 'message' => 'Unable to read disk space'];
            }

            $usedPercent = round((($total - $free) / $total) * 100, 1);
            $criticalThreshold = config('health.disk_critical_percent', 95);
            $warningThreshold = config('health.disk_warning_percent', 80);

            if ($usedPercent >= $criticalThreshold) {
                return ['status' => 'error', 'message' => 'Disk usage critical'];
            }

            if ($usedPercent >= $warningThreshold) {
                return ['status' => 'warning', 'message' => 'Disk usage high'];
            }

            return ['status' => 'ok', 'message' => 'Disk usage nominal'];
        });
    }

    public function timedCheck(callable $check): array
    {
        $start = microtime(true);

        try {
            $result = $check();
        } catch (\Throwable $e) {
            Log::error('Health check failed', ['error' => $e->getMessage()]);
            $result = ['status' => 'error', 'message' => 'Check failed'];
        }

        $result['response_time_ms'] = round((microtime(true) - $start) * 1000, 2);

        return $result;
    }
}
