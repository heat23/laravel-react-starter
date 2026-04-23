<?php

namespace App\Services;

use App\Enums\AuditEvent;
use App\Jobs\PersistAuditLog;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public function logLogin(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        $this->persist(AuditEvent::AUTH_LOGIN->value, $user?->id, []);
    }

    public function logLogout(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        $this->persist(AuditEvent::AUTH_LOGOUT->value, $user?->id, []);
    }

    /**
     * @param  array<string, mixed>  $extra  Additional metadata (e.g. UTM params, plan intent)
     */
    public function logRegistration(User $user, array $extra = []): void
    {
        $this->persist(AuditEvent::AUTH_REGISTER->value, $user->id, array_merge([
            'signup_source' => $user->signup_source ?? 'direct',
        ], $extra));
    }

    /**
     * Log a generic audit event. Metadata values should be scalar types
     * (strings, numbers, booleans) — never pass raw user input without sanitization.
     *
     * If the caller provides `user_id` in the context array, it is used as the
     * audit actor and stripped from the persisted metadata. Otherwise the
     * current `Auth::id()` is used (which is null in queue/webhook contexts).
     */
    public function log(AuditEvent|string $event, array $context = []): void
    {
        $eventName = $event instanceof AuditEvent ? $event->value : $event;

        $userId = Auth::id();
        if (array_key_exists('user_id', $context)) {
            $userId = $context['user_id'];
            unset($context['user_id']);
        }

        $this->persist($eventName, $userId, $context);
    }

    private function persist(string $event, ?int $userId, array $metadata = []): void
    {
        $ip = request()->ip();
        $userAgent = request()->userAgent();

        // Anonymize IP for non-security events when configured (GDPR Art. 5(1)(c) data minimisation).
        // Auth/security events retain full IP for abuse detection (GDPR Art. 6(1)(f) legitimate interest).
        if ($ip !== null && config('services.audit.ip_anonymization') && ! AuditLog::isSecurityEvent($event)) {
            $ip = AuditLog::anonymizeIp($ip);
        }

        // Idempotency key prevents duplicate rows when the job is retried or the
        // queue delivers the same dispatch twice. Bucketed to the current minute so
        // legitimate repeated events (e.g. login → login 90 seconds later) produce
        // distinct rows while exact retries within the same minute are deduplicated.
        $idempotencyKey = hash('sha256', $event.'|'.($userId ?? '').'|'.now()->startOfMinute()->timestamp.'|'.md5(json_encode($metadata) ?: ''));

        PersistAuditLog::dispatch($event, $userId, $ip, $userAgent, $metadata, $idempotencyKey);

        Log::channel('single')->info($event, [
            'event' => $event,
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'request_id' => request()?->attributes?->get('request_id'),
            'metadata' => $metadata,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
