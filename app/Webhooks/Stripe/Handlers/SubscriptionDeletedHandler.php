<?php

namespace App\Webhooks\Stripe\Handlers;

use App\Enums\AuditEvent;
use App\Jobs\PersistAuditLog;
use App\Models\EmailSendLog;
use App\Models\User;
use App\Notifications\InvoluntaryChurnWinBackNotification;
use App\Services\CacheInvalidationManager;
use App\Services\PlanLimitService;
use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\Dto\StripeEvent;
use Illuminate\Support\Facades\Log;

class SubscriptionDeletedHandler implements StripeEventHandler
{
    public function __construct(
        private PlanLimitService $planLimitService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function handle(StripeEvent $event): void
    {
        $payload = $event->payload;
        $cancellationReason = $payload['data']['object']['cancellation_details']['reason'] ?? null;
        $churnType = $cancellationReason === 'payment_failed' ? 'involuntary' : 'voluntary';

        Log::channel('single')->info('Stripe webhook: subscription.deleted', [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $payload['data']['object']['customer'] ?? null,
            'churn_type' => $churnType,
        ]);

        $this->invalidatePlanCache($payload);

        $customerId = $payload['data']['object']['customer'] ?? null;
        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $this->dispatchAudit($user, AuditEvent::SUBSCRIPTION_CANCELED, ['churn_type' => $churnType]);

                if ($cancellationReason === 'payment_failed' && ! EmailSendLog::alreadySent($user->id, 'involuntary_churn_win_back', 1)) {
                    $user->notify((new InvoluntaryChurnWinBackNotification)->delay(now()->addDays(3)));
                    EmailSendLog::record($user->id, 'involuntary_churn_win_back', 1);
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
