<?php

namespace App\Http\Middleware;

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
            $this->auditService->log('admin.unauthorized_access_attempt', [
                'route' => $request->route()?->getName(),
                'path' => $request->path(),
            ]);

            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
