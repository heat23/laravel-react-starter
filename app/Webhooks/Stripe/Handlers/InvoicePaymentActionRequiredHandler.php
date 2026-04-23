<?php

namespace App\Webhooks\Stripe\Handlers;

use App\Models\User;
use App\Notifications\PaymentActionRequiredNotification;
use App\Webhooks\Stripe\Contracts\StripeEventHandler;
use App\Webhooks\Stripe\Dto\StripeEvent;
use Illuminate\Support\Facades\Log;

class InvoicePaymentActionRequiredHandler implements StripeEventHandler
{
    use DeduplicatesStripeEvents;

    public function handle(StripeEvent $event): void
    {
        if ($this->alreadyProcessed($event->payload['id'] ?? '')) {
            return;
        }

        $payload = $event->payload;
        $customerId = $payload['data']['object']['customer'] ?? null;
        $hostedInvoiceUrl = $payload['data']['object']['hosted_invoice_url'] ?? null;
        $invoiceId = $payload['data']['object']['id'] ?? '';

        Log::channel('single')->info('Stripe webhook: invoice.payment_action_required', [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $customerId,
        ]);

        if ($customerId && $hostedInvoiceUrl) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $user->notify(new PaymentActionRequiredNotification(
                    hostedInvoiceUrl: $hostedInvoiceUrl,
                    invoiceId: $invoiceId,
                ));
            }
        }
    }
}
