<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! feature_enabled('onboarding', $request->user())) {
            return $next($request);
        }

        if (! $request->user()) {
            return $next($request);
        }

        if ($request->routeIs('onboarding')) {
            return $next($request);
        }

        // If user_settings is disabled, onboarding completion cannot be persisted — skip the check
        if (! config('features.user_settings.enabled', true)) {
            return $next($request);
        }

        $completed = $request->user()->getSetting('onboarding_completed');
        if (! $completed) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
