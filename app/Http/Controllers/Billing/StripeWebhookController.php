<?php

namespace App\Http\Controllers\Billing;

use App\Enums\AnalyticsEvent;
use App\Enums\LifecycleStage;
use App\Jobs\DispatchAnalyticsEvent;
use App\Jobs\PersistAuditLog;
use App\Models\EmailSendLog;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\InvoluntaryChurnWinBackNotification;
use App\Notifications\PaymentActionRequiredNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentRecoveredNotification;
use App\Notifications\RefundProcessedNotification;
use App\Services\CacheInvalidationManager;
use App\Services\LifecycleService;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Cache;
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

        $customerId = $payload['data']['object']['customer'] ?? null;
        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $this->dispatchWebhookAnalyticsEvent($user, AnalyticsEvent::SUBSCRIPTION_CREATED);
            }
        }

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

        // Detect a resume (cancel_at_period_end flipped from true → false).
        // SubscriptionController sets a short-lived cache key for user-initiated resumes so
        // this webhook handler skips the dispatch and avoids double-counting analytics.
        $previousCancelAtPeriodEnd = $payload['data']['previous_attributes']['cancel_at_period_end'] ?? null;
        $currentCancelAtPeriodEnd = $payload['data']['object']['cancel_at_period_end'] ?? false;
        if ($previousCancelAtPeriodEnd === true && $currentCancelAtPeriodEnd === false) {
            $customerId = $payload['data']['object']['customer'] ?? null;
            if ($customerId) {
                $user = User::where('stripe_id', $customerId)->first();
                if ($user) {
                    $cacheKey = "billing.resume_analytics_sent:{$user->id}";
                    if (! Cache::pull($cacheKey)) {
                        $this->dispatchWebhookAnalyticsEvent($user, AnalyticsEvent::SUBSCRIPTION_RESUMED);
                    }
                }
            }
        }

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

        $customerId = $payload['data']['object']['customer'] ?? null;
        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();
            if ($user) {
                $this->dispatchWebhookAnalyticsEvent($user, AnalyticsEvent::SUBSCRIPTION_CANCELED, [
                    'churn_type' => $churnType,
                ]);

                // Dispatch win-back notification for involuntary churn (payment failure exhaustion)
                if ($cancellationReason === 'payment_failed' && ! EmailSendLog::alreadySent($user->id, 'involuntary_churn_win_back', 1)) {
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

                    // Only notify and track when recovering from a past-due state
                    $user->notify(new PaymentRecoveredNotification(invoiceId: $invoiceId));
                    $this->dispatchWebhookAnalyticsEvent($user, AnalyticsEvent::BILLING_PAYMENT_RECOVERED, [
                        'invoice_id' => $invoiceId,
                    ]);
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

                $this->dispatchWebhookAnalyticsEvent($user, AnalyticsEvent::BILLING_PAYMENT_FAILED, [
                    'invoice_id' => $payload['data']['object']['id'] ?? null,
                ]);
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

                $this->dispatchWebhookAnalyticsEvent($user, AnalyticsEvent::BILLING_CHARGE_REFUNDED, [
                    'charge_id' => $payload['data']['object']['id'] ?? null,
                    'amount_refunded' => $payload['data']['object']['amount_refunded'] ?? 0,
                    'currency' => $payload['data']['object']['currency'] ?? 'usd',
                ]);
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

        Log::channel('single')->info("Stripe webhook: {$action}", array_merge($extraMeta, [
            'event_id' => $payload['id'] ?? null,
            'stripe_customer' => $stripeCustomerId,
        ]));
    }

    /**
     * Dispatch a billing analytics event for a known user, bypassing the
     * AuditService routing which uses Auth::id() (null in webhook context).
     *
     * @param  array<string, mixed>  $params
     */
    private function dispatchWebhookAnalyticsEvent(User $user, AnalyticsEvent $event, array $params = []): void
    {
        try {
            // Persist to audit_logs with the correct enum value and user_id so that
            // ProductAnalyticsService::getSubscriptionEvents() can find webhook events.
            // Auth::id() is null in webhook context so both jobs receive explicit user_id.
            PersistAuditLog::dispatch($event->value, $user->id, null, null, $params ?: null);
            DispatchAnalyticsEvent::dispatch($event->value, $params, $user->id);
        } catch (\Throwable) {
            // Analytics dispatch should never break webhook processing
        }
    }
}
