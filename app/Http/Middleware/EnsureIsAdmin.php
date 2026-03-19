<?php

namespace App\Http\Middleware;

use App\Enums\AnalyticsEvent;
use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function __construct(
        private AuditService $auditService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            $this->auditService->log(AnalyticsEvent::ADMIN_UNAUTHORIZED_ACCESS, [
                'route' => $request->route()?->getName(),
                'path' => $request->path(),
            ]);

            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
