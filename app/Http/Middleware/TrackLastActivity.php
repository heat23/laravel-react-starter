<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActivity
{
    /**
     * Update the authenticated user's last_active_at timestamp.
     *
     * Throttled to once per config('app.activity_tracking_window') minutes to reduce write pressure.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $this->shouldUpdate($user)) {
            $user->forceFill(['last_active_at' => now()])->saveQuietly();
        }

        return $next($request);
    }

    private function shouldUpdate(mixed $user): bool
    {
        $lastActive = $user->last_active_at ?? null;

        if ($lastActive === null) {
            return true;
        }

        return $lastActive->lt(now()->subMinutes(config('app.activity_tracking_window', 15)));
    }
}
