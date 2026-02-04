<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Audit Service
 *
 * Simple audit logging for security-relevant events.
 * Logs to Laravel's default logger (storage/logs/laravel.log).
 *
 * For production, consider:
 * - Storing audits in database (audit_logs table)
 * - Using dedicated audit packages (owen-it/laravel-auditing)
 * - Sending to external audit services (Datadog, Papertrail)
 */
class AuditService
{
    /**
     * Log a user login event.
     */
    public function logLogin(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        Log::channel('single')->info('User logged in', [
            'event' => 'auth.login',
            'user_id' => $user?->id,
            'email' => $user?->email,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log a user logout event.
     */
    public function logLogout(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        Log::channel('single')->info('User logged out', [
            'event' => 'auth.logout',
            'user_id' => $user?->id,
            'email' => $user?->email,
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log a user registration event.
     */
    public function logRegistration(User $user): void
    {
        Log::channel('single')->info('User registered', [
            'event' => 'auth.register',
            'user_id' => $user->id,
            'email' => $user->email,
            'signup_source' => $user->signup_source ?? 'direct',
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log a generic audit event.
     *
     * Use this for custom events that don't fit the standard patterns.
     */
    public function log(string $event, array $context = []): void
    {
        $defaultContext = [
            'event' => $event,
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('single')->info($event, array_merge($defaultContext, $context));
    }
}
