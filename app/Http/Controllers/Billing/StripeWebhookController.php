<?php

namespace App\Http\Controllers\Billing;

use App\Services\AuditService;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends WebhookController
{
    public function __construct()
    {
        if (! app()->environment('local') && empty(config('cashier.webhook.secret'))) {
            throw new \RuntimeException(
                'STRIPE_WEBHOOK_SECRET must be set in non-local environments. '
                .'Without it, webhook signature verification is disabled, allowing forged events.'
            );
        }

        parent::__construct();
    }

    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionCreated($payload);

        $this->logWebhookEvent('subscription.created', $payload);

        return $response;
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        $this->logWebhookEvent('subscription.updated', $payload);

        return $response;
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $this->logWebhookEvent('subscription.deleted', $payload);

        return $response;
    }

    protected function handleCustomerSubscriptionTrialWillEnd(array $payload): Response
    {
        $this->logWebhookEvent('subscription.trial_will_end', $payload);

        return $this->successMethod();
    }

    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        $this->logWebhookEvent('invoice.payment_succeeded', $payload);

        return $this->successMethod();
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $this->logWebhookEvent('invoice.payment_failed', $payload);

        return $this->successMethod();
    }

    protected function handleInvoicePaymentActionRequired(array $payload): Response
    {
        $this->logWebhookEvent('invoice.payment_action_required', $payload);

        return $this->successMethod();
    }

    protected function handleCustomerUpdated(array $payload): Response
    {
        $this->logWebhookEvent('customer.updated', $payload);

        return $this->successMethod();
    }

    private function logWebhookEvent(string $action, array $payload): void
    {
        $stripeCustomerId = $payload['data']['object']['customer']
            ?? $payload['data']['object']['id']
            ?? null;

        Log::channel('single')->info("Stripe webhook: {$action}", [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $stripeCustomerId,
        ]);

        try {
            app(AuditService::class)->log("stripe.{$action}", [
                'event_id' => $payload['id'] ?? null,
                'event_type' => $payload['type'] ?? null,
                'stripe_customer' => $stripeCustomerId,
            ]);
        } catch (\Throwable) {
            // Audit logging should never break webhook processing
        }
    }
}
