<?php

namespace App\Webhooks\Stripe\Handlers;

use App\Enums\AuditEvent;
use App\Jobs\PersistAuditLog;
use App\Models\User;
use App\Services\CacheInvalidationManager;
use App\Services\PlanLimitService;
use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\Dto\StripeEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SubscriptionUpdatedHandler implements StripeEventHandler
{
    public function __construct(
        private PlanLimitService $planLimitService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    use DeduplicatesStripeEvents;

    public function handle(StripeEvent $event): void
    {
        if ($this->alreadyProcessed($event->payload['id'] ?? '')) {
            return;
        }

        $payload = $event->payload;

        Log::channel('single')->info('Stripe webhook: subscription.updated', [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $payload['data']['object']['customer'] ?? null,
        ]);

        $this->invalidatePlanCache($payload);

        $previousCancelAtPeriodEnd = $payload['data']['previous_attributes']['cancel_at_period_end'] ?? null;
        $currentCancelAtPeriodEnd = $payload['data']['object']['cancel_at_period_end'] ?? false;

        if ($previousCancelAtPeriodEnd === true && $currentCancelAtPeriodEnd === false) {
            $customerId = $payload['data']['object']['customer'] ?? null;
            if ($customerId) {
                $user = User::where('stripe_id', $customerId)->first();
                if ($user) {
                    $cacheKey = "billing.resume_analytics_sent:{$user->id}";
                    if (! Cache::pull($cacheKey)) {
                        $this->dispatchAudit($user, AuditEvent::SUBSCRIPTION_RESUMED);
                    }
                }
            }
        }
    }

    private function invalidatePlanCache(array $payload): void
    {
        try {
            $customerId = $payload['data']['object']['customer'] ?? $payload['data']['object']['id'] ?? null;
            if ($customerId) {
                $user = User::where('stripe_id', $customerId)->first();
                if ($user) {
                    $this->planLimitService->invalidateUserPlanCache($user);
                }
            }
            $this->cacheManager->invalidateBilling();
        } catch (\Throwable) {
        }
    }

    private function dispatchAudit(User $user, AuditEvent $event, array $params = []): void
    {
        try {
            PersistAuditLog::dispatch($event->value, $user->id, null, null, $params ?: null);
        } catch (\Throwable) {
        }
    }
}
