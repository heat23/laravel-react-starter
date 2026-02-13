<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthCheckController extends Controller
{
    public function __construct(
        private HealthCheckService $healthCheckService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $result = $this->healthCheckService->runAllChecks();
        $httpCode = $result['status'] === 'unhealthy' ? 503 : 200;

        return response()->json($result, $httpCode);
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
}
