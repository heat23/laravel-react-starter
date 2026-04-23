<?php

namespace App\Webhooks\Stripe\Handlers;

use App\Enums\AuditEvent;
use App\Jobs\PersistAuditLog;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\PaymentRecoveredNotification;
use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\Dto\StripeEvent;
use Illuminate\Support\Facades\Log;

class InvoicePaymentSucceededHandler implements StripeEventHandler
{
    public function handle(StripeEvent $event): void
    {
        $payload = $event->payload;
        $customerId = $payload['data']['object']['customer'] ?? null;
        $subscriptionId = $payload['data']['object']['subscription'] ?? null;
        $invoiceId = $payload['data']['object']['id'] ?? '';

        Log::channel('single')->info('Stripe webhook: invoice.payment_succeeded', [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $customerId,
        ]);

        if ($customerId && $subscriptionId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $subscription = Subscription::where('stripe_id', $subscriptionId)->first();
                if ($subscription && $subscription->past_due_since !== null) {
                    $subscription->past_due_since = null;
                    $subscription->save();

                    $user->notify(new PaymentRecoveredNotification(invoiceId: $invoiceId));
                    $this->dispatchAudit($user, AuditEvent::BILLING_PAYMENT_RECOVERED, ['invoice_id' => $invoiceId]);
                }
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
