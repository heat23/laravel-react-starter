<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $retryAfter = $response->headers->get('Retry-After');

        if ($retryAfter !== null) {
            $response->headers->set('X-RateLimit-Reset',
                (string) (time() + (int) $retryAfter));
        }

        return $response;
    }
}
