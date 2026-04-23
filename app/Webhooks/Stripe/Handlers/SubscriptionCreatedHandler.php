<?php

namespace App\Webhooks\Stripe\Handlers;

use App\Enums\AuditEvent;
use App\Jobs\PersistAuditLog;
use App\Models\User;
use App\Services\CacheInvalidationManager;
use App\Services\PlanLimitService;
use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\Dto\StripeEvent;
use Illuminate\Support\Facades\Log;

class SubscriptionCreatedHandler implements StripeEventHandler
{
    public function __construct(
        private PlanLimitService $planLimitService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function handle(StripeEvent $event): void
    {
        $payload = $event->payload;

        Log::channel('single')->info('Stripe webhook: subscription.created', [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $payload['data']['object']['customer'] ?? null,
        ]);

        $this->invalidatePlanCache($payload);

        $customerId = $payload['data']['object']['customer'] ?? null;
        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $this->dispatchAudit($user, AuditEvent::SUBSCRIPTION_CREATED);
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
