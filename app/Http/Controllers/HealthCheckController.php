<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class HealthCheckController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'disk' => $this->checkDisk(),
        ];

        $statuses = array_column($checks, 'status');
        $overallStatus = match (true) {
            in_array('error', $statuses) => 'unhealthy',
            in_array('warning', $statuses) => 'degraded',
            default => 'healthy',
        };

        $httpCode = $overallStatus === 'unhealthy' ? 503 : 200;

        return response()->json([
            'status' => $overallStatus,
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ], $httpCode);
    }

    /**
     * Authorization priority: token > IP allowlist > local environment.
     * If a token is configured, only token auth is checked (IP list is ignored).
     * If no token but IPs are configured, only IP auth is checked.
     * If neither is configured, access is allowed only in local environment.
     */
    private function isAuthorized(Request $request): bool
    {
        $token = config('health.token');
        $allowedIps = config('health.allowed_ips');

        if ($token !== null && $token !== '') {
            $provided = $request->bearerToken() ?? $request->query('token');

            return hash_equals($token, $provided ?? '');
        }

        if ($allowedIps) {
            $ips = array_map('trim', explode(',', $allowedIps));

            return in_array($request->ip(), $ips);
        }

        return app()->isLocal();
    }

    private function checkDatabase(): array
    {
        return $this->timedCheck(function () {
            DB::select('SELECT 1');

            return ['status' => 'ok', 'message' => 'Connection successful'];
        });
    }

    private function checkCache(): array
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

    private function checkQueue(): array
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

    private function checkDisk(): array
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

    private function timedCheck(callable $check): array
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
