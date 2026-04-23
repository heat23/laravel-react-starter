<?php

namespace App\Webhooks\Stripe\Handlers;

use App\Enums\AuditEvent;
use App\Jobs\PersistAuditLog;
use App\Models\User;
use App\Notifications\RefundProcessedNotification;
use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\Dto\StripeEvent;
use Illuminate\Support\Facades\Log;

class ChargeRefundedHandler implements StripeEventHandler
{
    public function handle(StripeEvent $event): void
    {
        $payload = $event->payload;
        $customerId = $payload['data']['object']['customer'] ?? null;

        Log::channel('single')->info('Stripe webhook: charge.refunded', [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $customerId,
        ]);

        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $user->notify(new RefundProcessedNotification(
                    chargeId: $payload['data']['object']['id'] ?? '',
                    amountRefunded: $payload['data']['object']['amount_refunded'] ?? 0,
                    currency: $payload['data']['object']['currency'] ?? 'usd',
                    reason: $payload['data']['object']['refunds']['data'][0]['reason'] ?? null,
                ));

                $this->dispatchAudit($user, AuditEvent::BILLING_CHARGE_REFUNDED, [
                    'charge_id' => $payload['data']['object']['id'] ?? null,
                    'amount_refunded' => $payload['data']['object']['amount_refunded'] ?? 0,
                    'currency' => $payload['data']['object']['currency'] ?? 'usd',
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
