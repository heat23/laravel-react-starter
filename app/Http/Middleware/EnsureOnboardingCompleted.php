<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('features.onboarding.enabled', false)) {
            return $next($request);
        }

        if (! $request->user()) {
            return $next($request);
        }

        if ($request->routeIs('onboarding')) {
            return $next($request);
        }

        $completed = $request->user()->getSetting('onboarding_completed');
        if (! $completed) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
