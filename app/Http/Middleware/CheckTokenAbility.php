<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce token ability requirements on API routes.
 *
 * When the request is authenticated via a Sanctum personal access token,
 * this middleware checks that the token has all of the required abilities.
 * Session-authenticated requests (TransientToken) bypass the check because
 * TransientToken::can() always returns true.
 */
class CheckTokenAbility
{
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        foreach ($abilities as $ability) {
            if (! $user->tokenCan($ability)) {
                abort(403, 'Forbidden');
            }
        }

        return $next($request);
    }
}
