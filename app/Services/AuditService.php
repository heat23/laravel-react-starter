<?php

namespace App\Services;

use App\Enums\AnalyticsEvent;
use App\Jobs\DispatchAnalyticsEvent;
use App\Jobs\PersistAuditLog;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * UserSetting key that records the user's analytics consent decision
     * (persisted by ConsentController when the cookie banner is actioned).
     * Stored as a JSON boolean: true = consented, false = declined.
     */
    public const ANALYTICS_CONSENT_KEY = 'analytics_consent';

    /**
     * High-value server-side events forwarded to GA4 via Measurement Protocol.
     * Keep this list intentionally small — only lifecycle events with clear
     * product impact. Admin-only events should NOT go to GA4.
     */
    private const GA4_FORWARDED_EVENTS = [
        'auth.register',
        'auth.login',
        'auth.social_login',
        'onboarding.completed',
        'subscription.created',
        'subscription.canceled',
        'trial.started',
        'limit.threshold_50',
        'limit.threshold_80',
        'limit.threshold_100',
        'billing.payment_failed',
        'billing.payment_method_updated',
        'billing.payment_recovered',
        'billing.retention_coupon_applied',
    ];

    public function logLogin(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        $this->persist(AnalyticsEvent::AUTH_LOGIN->value, $user?->id, []);
    }

    public function logLogout(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        $this->persist(AnalyticsEvent::AUTH_LOGOUT->value, $user?->id, []);
    }

    /**
     * @param  array<string, mixed>  $extra  Additional metadata (e.g. UTM params, plan intent)
     */
    public function logRegistration(User $user, array $extra = []): void
    {
        $this->persist(AnalyticsEvent::AUTH_REGISTER->value, $user->id, array_merge([
            'signup_source' => $user->signup_source ?? 'direct',
        ], $extra));
    }

    /**
     * Log a generic audit event. Metadata values should be scalar types
     * (strings, numbers, booleans) — never pass raw user input without sanitization.
     */
    public function log(AnalyticsEvent|string $event, array $context = []): void
    {
        $eventName = $event instanceof AnalyticsEvent ? $event->value : $event;

        $this->persist($eventName, Auth::id(), $context);
    }

    /**
     * Log a product analytics event with enriched user context.
     * Adds plan tier, signup cohort, and activation status automatically.
     */
    public function logProductEvent(AnalyticsEvent|string $event, ?User $user = null, array $context = []): void
    {
        $user = $user ?? Auth::user();
        $eventName = $event instanceof AnalyticsEvent ? $event->value : $event;

        $enriched = array_merge($context, $this->getProductContext($user));

        $this->persist($eventName, $user?->id, $enriched);
    }

    /**
     * Build product context for analytics enrichment.
     */
    private function getProductContext(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $planTier = 'free';
        if (config('features.billing.enabled')) {
            $planLimitService = app(PlanLimitService::class);
            $planTier = $planLimitService->getUserPlan($user);
        }

        return [
            'plan_tier' => $planTier,
            'signup_cohort' => $user->created_at?->format('Y-m'),
            'is_activated' => $user->getSetting('onboarding_completed') !== null,
        ];
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

        PersistAuditLog::dispatch($event, $userId, $ip, $userAgent, $metadata);

        Log::channel('single')->info($event, [
            'event' => $event,
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'request_id' => request()?->attributes?->get('request_id'),
            'metadata' => $metadata,
            'timestamp' => now()->toISOString(),
        ]);

        // Forward high-value lifecycle events to GA4 via Measurement Protocol (async job).
        // userId must be non-null — anonymous events have no GA4 client_id anchor.
        // Consent gate: skip forwarding only when the user has EXPLICITLY declined analytics
        // consent via the cookie banner. When consent is unknown/unset (null), we forward under
        // a legitimate-interest legal basis — server-side lifecycle analytics for authenticated
        // users qualify under GDPR Art. 6(1)(f) and do not require prior consent. Explicit
        // decline (false) is always honoured.
        if ($userId !== null && in_array($event, self::GA4_FORWARDED_EVENTS, true)) {
            if (! $this->userDeclinedAnalytics($userId)) {
                DispatchAnalyticsEvent::dispatch($event, $metadata, $userId);
            }
        }
    }

    /**
     * Returns true only when the user has explicitly declined analytics consent.
     * null/unset = legitimate interest applies, so returns false.
     */
    private function userDeclinedAnalytics(int $userId): bool
    {
        // Re-use the already-hydrated Auth user to avoid an extra SELECT on every call.
        // Falls back to User::find for background jobs where Auth is not the event owner.
        $user = Auth::id() === $userId ? Auth::user() : User::find($userId);

        if (! $user) {
            return false;
        }

        $consent = $user->getSetting(self::ANALYTICS_CONSENT_KEY);

        // PHP bool false (canonical JSON round-trip) = explicitly declined.
        // String 'false' / int 0 are defensive fallbacks against corrupted or
        // pre-migration stored values that bypassed json_decode.
        return $consent === false || $consent === 'false' || $consent === 0;
    }
}
