<?php

namespace App\Webhooks\Stripe\Handlers;

use App\Enums\AuditEvent;
use App\Enums\LifecycleStage;
use App\Jobs\PersistAuditLog;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\PaymentFailedNotification;
use App\Services\LifecycleService;
use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\Dto\StripeEvent;
use Illuminate\Support\Facades\Log;

class InvoicePaymentFailedHandler implements StripeEventHandler
{
    use DeduplicatesStripeEvents;

    public function handle(StripeEvent $event): void
    {
        if ($this->alreadyProcessed($event->payload['id'] ?? '')) {
            return;
        }

        $payload = $event->payload;
        $customerId = $payload['data']['object']['customer'] ?? null;

        Log::channel('single')->info('Stripe webhook: invoice.payment_failed', [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $customerId,
        ]);

        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $user->notify(new PaymentFailedNotification(
                    invoiceId: $payload['data']['object']['id'] ?? '',
                    subscriptionId: $payload['data']['object']['subscription'] ?? '',
                ));

                try {
                    app(LifecycleService::class)->transition($user, LifecycleStage::AT_RISK, 'invoice_payment_failed');
                } catch (\Throwable) {
                }

                $stripeSubscriptionId = $payload['data']['object']['subscription'] ?? null;
                if ($stripeSubscriptionId) {
                    $subscription = Subscription::where('stripe_id', $stripeSubscriptionId)->first();
                    if ($subscription && $subscription->past_due_since === null) {
                        $subscription->past_due_since = now();
                        $subscription->save();
                    }
                }

                $this->dispatchAudit($user, AuditEvent::BILLING_PAYMENT_FAILED, [
                    'invoice_id' => $payload['data']['object']['id'] ?? null,
                ]);
            }
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
