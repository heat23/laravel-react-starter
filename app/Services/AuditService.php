<?php

namespace App\Services;

use App\Jobs\PersistAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public function logLogin(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        $this->persist('auth.login', $user?->id, [
            'email' => $user?->email,
        ]);
    }

    public function logLogout(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        $this->persist('auth.logout', $user?->id, [
            'email' => $user?->email,
        ]);
    }

    public function logRegistration(User $user): void
    {
        $this->persist('auth.register', $user->id, [
            'email' => $user->email,
            'signup_source' => $user->signup_source ?? 'direct',
        ]);
    }

    /**
     * Log a generic audit event. Metadata values should be scalar types
     * (strings, numbers, booleans) — never pass raw user input without sanitization.
     */
    public function log(string $event, array $context = []): void
    {
        $this->persist($event, Auth::id(), $context);
    }

    /**
     * Log a product analytics event with enriched user context.
     * Adds plan tier, signup cohort, and activation status automatically.
     */
    public function logProductEvent(string $event, ?User $user = null, array $context = []): void
    {
        $user = $user ?? Auth::user();

        $enriched = array_merge($context, $this->getProductContext($user));

        $this->persist($event, $user?->id, $enriched);
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
    }
}
