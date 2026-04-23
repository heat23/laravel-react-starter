<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEvent;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\DataHealthService;
use Inertia\Inertia;
use Inertia\Response;

class AdminDataHealthController extends Controller
{
    public function __construct(
        private AuditService $auditService,
    ) {}

    public function index(DataHealthService $dataHealth): Response
    {
        $checks = $dataHealth->runAllChecks();

        $this->auditService->log(AuditEvent::ADMIN_DATA_HEALTH_VIEWED, [
            'check_count' => count($checks),
        ]);

        return Inertia::render('App/Admin/DataHealth', [
            'checks' => $checks,
            'ran_at' => now()->toISOString(),
        ]);
    }
}
