<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HealthCheckService;
use Inertia\Inertia;
use Inertia\Response;

class AdminHealthController extends Controller
{
    public function __construct(
        private HealthCheckService $healthCheckService,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('Admin/Health', [
            'health' => $this->healthCheckService->runAllChecks(),
        ]);
    }
}
