<?php

namespace App\Http\Controllers\Billing;

use App\Enums\LifecycleStage;
use App\Models\EmailSendLog;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\InvoluntaryChurnWinBackNotification;
use App\Notifications\PaymentActionRequiredNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentRecoveredNotification;
use App\Notifications\RefundProcessedNotification;
use App\Services\AuditService;
use App\Services\CacheInvalidationManager;
use App\Services\LifecycleService;
use App\Services\PlanLimitService;
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
        $this->invalidatePlanCache($payload);

        return $response;
    }

    protected function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $subscriptionId = $payload['data']['object']['id'] ?? null;
        $eventTimestamp = $payload['created'] ?? null;

        // Reject out-of-order webhook events
        if ($subscriptionId && $eventTimestamp) {
            $subscription = Subscription::where('stripe_id', $subscriptionId)->first();

            if ($subscription && $subscription->last_webhook_at && $eventTimestamp <= $subscription->last_webhook_at) {
                Log::warning('Out-of-order webhook rejected', [
                    'subscription_id' => $subscriptionId,
                    'event_timestamp' => $eventTimestamp,
                    'last_processed_at' => $subscription->last_webhook_at,
                ]);

                return $this->successMethod();
            }
        }

        $response = parent::handleCustomerSubscriptionUpdated($payload);

        // Update sequence tracking after successful processing
        if ($subscriptionId && $eventTimestamp) {
            Subscription::where('stripe_id', $subscriptionId)
                ->update(['last_webhook_at' => $eventTimestamp]);
        }

        $this->logWebhookEvent('subscription.updated', $payload);
        $this->invalidatePlanCache($payload);

        return $response;
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $cancellationReason = $payload['data']['object']['cancellation_details']['reason'] ?? null;
        $churnType = $cancellationReason === 'payment_failed' ? 'involuntary' : 'voluntary';

        $this->logWebhookEvent('subscription.deleted', $payload, [
            'churn_type' => $churnType,
        ]);
        $this->invalidatePlanCache($payload);

        // Dispatch win-back notification for involuntary churn (payment failure exhaustion)
        if ($cancellationReason === 'payment_failed') {
            $customerId = $payload['data']['object']['customer'] ?? null;
            if ($customerId) {
                $user = User::where('stripe_id', $customerId)->first();
                if ($user && ! EmailSendLog::alreadySent($user->id, 'involuntary_churn_win_back', 1)) {
                    $user->notify((new InvoluntaryChurnWinBackNotification)->delay(now()->addDays(3)));
                    EmailSendLog::record($user->id, 'involuntary_churn_win_back', 1);
                }
            }
        }

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

        $customerId = $payload['data']['object']['customer'] ?? null;
        $subscriptionId = $payload['data']['object']['subscription'] ?? null;
        $invoiceId = $payload['data']['object']['id'] ?? '';

        if ($customerId && $subscriptionId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                // Clear past_due_since to stop dunning sequence
                $subscription = Subscription::where('stripe_id', $subscriptionId)->first();
                if ($subscription && $subscription->past_due_since !== null) {
                    $subscription->past_due_since = null;
                    $subscription->save();

                    // Only notify when recovering from a past-due state
                    $user->notify(new PaymentRecoveredNotification(invoiceId: $invoiceId));
                }
            }
        }

        return $this->successMethod();
    }

    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $this->logWebhookEvent('invoice.payment_failed', $payload);

        $customerId = $payload['data']['object']['customer'] ?? null;
        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $user->notify(new PaymentFailedNotification(
                    invoiceId: $payload['data']['object']['id'] ?? '',
                    subscriptionId: $payload['data']['object']['subscription'] ?? '',
                ));

                // Transition to at_risk lifecycle stage
                try {
                    app(LifecycleService::class)->transition($user, LifecycleStage::AT_RISK, 'invoice_payment_failed');
                } catch (\Throwable) {
                }

                // Stamp past_due_since on the subscription so dunning uses reliable timing
                $stripeSubscriptionId = $payload['data']['object']['subscription'] ?? null;
                if ($stripeSubscriptionId) {
                    $subscription = Subscription::where('stripe_id', $stripeSubscriptionId)->first();
                    if ($subscription && $subscription->past_due_since === null) {
                        $subscription->past_due_since = now();
                        $subscription->save();
                    }
                }
            }
        }

        return $this->successMethod();
    }

    protected function handleInvoicePaymentActionRequired(array $payload): Response
    {
        $this->logWebhookEvent('invoice.payment_action_required', $payload);

        $customerId = $payload['data']['object']['customer'] ?? null;
        $hostedInvoiceUrl = $payload['data']['object']['hosted_invoice_url'] ?? null;
        $invoiceId = $payload['data']['object']['id'] ?? '';

        if ($customerId && $hostedInvoiceUrl) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $user->notify(new PaymentActionRequiredNotification(
                    hostedInvoiceUrl: $hostedInvoiceUrl,
                    invoiceId: $invoiceId,
                ));
            }
        }

        return $this->successMethod();
    }

    protected function handleCustomerUpdated(array $payload): Response
    {
        $this->logWebhookEvent('customer.updated', $payload);

        return $this->successMethod();
    }

    protected function handleChargeRefunded(array $payload): Response
    {
        $this->logWebhookEvent('charge.refunded', $payload);

        $customerId = $payload['data']['object']['customer'] ?? null;
        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $user->notify(new RefundProcessedNotification(
                    chargeId: $payload['data']['object']['id'] ?? '',
                    amountRefunded: $payload['data']['object']['amount_refunded'] ?? 0,
                    currency: $payload['data']['object']['currency'] ?? 'usd',
                    reason: $payload['data']['object']['refunds']['data'][0]['reason'] ?? null,
                ));
            }
        }

        return $this->successMethod();
    }

    private function invalidatePlanCache(array $payload): void
    {
        try {
            $customerId = $payload['data']['object']['customer']
                ?? $payload['data']['object']['id']
                ?? null;

            if ($customerId) {
                $user = User::where('stripe_id', $customerId)->first();
                if ($user) {
                    app(PlanLimitService::class)->invalidateUserPlanCache($user);
                }
            }

            app(CacheInvalidationManager::class)->invalidateBilling();
        } catch (\Throwable) {
            // Cache invalidation should never break webhook processing
        }
    }

    /**
     * @param  array<string, mixed>  $extraMeta
     */
    private function logWebhookEvent(string $action, array $payload, array $extraMeta = []): void
    {
        $stripeCustomerId = $payload['data']['object']['customer']
            ?? $payload['data']['object']['id']
            ?? null;

        Log::channel('single')->info("Stripe webhook: {$action}", [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $stripeCustomerId,
        ]);

        try {
            app(AuditService::class)->log("stripe.{$action}", array_merge([
                'event_id' => $payload['id'] ?? null,
                'event_type' => $payload['type'] ?? null,
                'stripe_customer' => $stripeCustomerId,
            ], $extraMeta));
        } catch (\Throwable) {
            // Audit logging should never break webhook processing
        }
    }
}
