<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $incoming = $request->header('X-Request-Id');
        $requestId = ($incoming && preg_match('/^[a-zA-Z0-9\-]{1,64}$/', $incoming))
            ? $incoming
            : (string) Str::uuid();

        $request->attributes->set('request_id', $requestId);

        Log::shareContext(['request_id' => $requestId]);

        if (class_exists('\Sentry\SentrySdk')) {
            \Sentry\configureScope(fn (\Sentry\State\Scope $scope) => $scope->setTag('request_id', $requestId));
        }

        $response = $next($request);

        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
