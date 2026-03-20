<?php

namespace App\Http\Controllers\Billing;

use App\Enums\AnalyticsEvent;
use App\Exceptions\ConcurrentOperationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CancelSubscriptionRequest;
use App\Http\Requests\Billing\SubscribeRequest;
use App\Http\Requests\Billing\SwapPlanRequest;
use App\Http\Requests\Billing\UpdatePaymentMethodRequest;
use App\Http\Requests\Billing\UpdateQuantityRequest;
use App\Services\AuditService;
use App\Services\BillingService;
use App\Services\CacheInvalidationManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\ApiErrorException;

class SubscriptionController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
    ) {}

    public function subscribe(SubscribeRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing('subscriptions.items');

        if ($user->subscribed('default')) {
            return back()->with('error', 'You already have an active subscription. Use plan swap to change plans.');
        }

        $priceId = $request->validated('price_id');
        $quantity = $request->validated('quantity', 1);

        $tier = $this->billingService->resolveTierFromPrice($priceId);

        if ($tier === null) {
            return back()->with('error', 'Invalid plan selected.');
        }

        $tierConfig = config("plans.{$tier}");
        if (! empty($tierConfig['coming_soon'] ?? false) || config('features.billing.coming_soon', false)) {
            return back()->with('error', 'This plan is coming soon and not yet available for purchase.');
        }

        $seatError = $this->billingService->validateSeatCount($tier, $quantity);
        if ($seatError) {
            return back()->with('error', $seatError);
        }

        try {
            $this->billingService->createSubscription(
                $user,
                $priceId,
                $request->validated('payment_method'),
                $request->validated('coupon'),
                $quantity,
            );

            $tierConfig = config("plans.{$tier}");
            $amount = (float) ($tierConfig['price_monthly'] ?? 0) * $quantity;

            $this->auditService->log(AnalyticsEvent::SUBSCRIPTION_CREATED, [
                'user_id' => $user->id,
                'price_id' => $priceId,
                'tier' => $tier,
                'quantity' => $quantity,
                'amount' => $amount,
            ]);

            $this->invalidateAdminCaches();

            return redirect()->route('billing.index', ['checkout' => 'success'])->with('success', 'Subscription created successfully.');
        } catch (ConcurrentOperationException) {
            return back()->with('error', 'A subscription request is already in progress. Please try again.');
        } catch (IncompletePayment $e) {
            return back()->with('error', 'Payment requires additional confirmation. Please try again.');
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during subscription creation', [
                'user_id' => $user->id,
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $this->friendlyStripeError($e));
        }
    }

    public function cancel(CancelSubscriptionRequest $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('subscriptions.items');
        $immediately = $request->validated('immediately', false);

        if (! $user->subscribed('default')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No active subscription to cancel.'], 400);
            }

            return back()->with('error', 'No active subscription to cancel.');
        }

        try {
            $this->billingService->cancelSubscription($user, $immediately);

            $this->auditService->log(AnalyticsEvent::SUBSCRIPTION_CANCELED, array_filter([
                'user_id' => $user->id,
                'immediately' => $immediately,
                'reason' => $request->validated('reason'),
                'feedback' => $request->validated('feedback'),
            ], fn ($v) => $v !== null));

            $this->invalidateAdminCaches();

            $message = $immediately
                ? 'Subscription canceled immediately.'
                : 'Subscription will be canceled at the end of the billing period.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message]);
            }

            return redirect()->route('billing.index')->with('success', $message);
        } catch (ConcurrentOperationException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'A cancellation request is already in progress. Please try again.'], 409);
            }

            return back()->with('error', 'A cancellation request is already in progress. Please try again.');
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during cancellation', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $this->friendlyStripeError($e)], 500);
            }

            return back()->with('error', $this->friendlyStripeError($e));
        }
    }

    public function resume(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('subscriptions.items');

        $subscription = $user->subscription('default');
        if (! $subscription || ! $subscription->onGracePeriod()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No canceled subscription to resume.'], 400);
            }

            return back()->with('error', 'No canceled subscription to resume.');
        }

        try {
            $this->billingService->resumeSubscription($user);

            $this->auditService->log(AnalyticsEvent::SUBSCRIPTION_RESUMED, [
                'user_id' => $user->id,
            ]);

            $this->invalidateAdminCaches();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Subscription resumed successfully.']);
            }

            return redirect()->route('billing.index')->with('success', 'Subscription resumed successfully.');
        } catch (ConcurrentOperationException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'A resume request is already in progress. Please try again.'], 409);
            }

            return back()->with('error', 'A resume request is already in progress. Please try again.');
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during resume', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $this->friendlyStripeError($e)], 500);
            }

            return back()->with('error', $this->friendlyStripeError($e));
        }
    }

    public function swap(SwapPlanRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing('subscriptions.items');

        if (! $user->subscribed('default')) {
            return back()->with('error', 'No active subscription to change.');
        }

        $newPriceId = $request->validated('price_id');

        try {
            $this->billingService->swapPlan($user, $newPriceId);

            $newTier = $this->billingService->resolveTierFromPrice($newPriceId) ?? 'unknown';

            $this->auditService->log(AnalyticsEvent::SUBSCRIPTION_SWAPPED, [
                'user_id' => $user->id,
                'new_price_id' => $newPriceId,
                'new_tier' => $newTier,
            ]);

            $this->invalidateAdminCaches();

            return redirect()->route('billing.index')->with('success', 'Plan updated successfully.');
        } catch (ConcurrentOperationException) {
            return back()->with('error', 'A plan change is already in progress. Please try again.');
        } catch (IncompletePayment $e) {
            return back()->with('error', 'Payment requires additional confirmation. Please try again.');
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during plan swap', [
                'user_id' => $user->id,
                'new_price_id' => $newPriceId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $this->friendlyStripeError($e));
        }
    }

    public function updateQuantity(UpdateQuantityRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing('subscriptions.items');

        if (! $user->subscribed('default')) {
            return back()->with('error', 'No active subscription.');
        }

        $quantity = $request->validated('quantity');
        $tier = $this->billingService->resolveUserTier($user);

        $seatError = $this->billingService->validateSeatCount($tier, $quantity);
        if ($seatError) {
            return back()->with('error', $seatError);
        }

        try {
            $this->billingService->updateQuantity($user, $quantity);

            $this->auditService->log(AnalyticsEvent::SUBSCRIPTION_QUANTITY_UPDATED, [
                'user_id' => $user->id,
                'quantity' => $quantity,
            ]);

            $this->invalidateAdminCaches();

            return redirect()->route('billing.index')->with('success', 'Seat count updated successfully.');
        } catch (ConcurrentOperationException) {
            return back()->with('error', 'A quantity update is already in progress. Please try again.');
        } catch (IncompletePayment $e) {
            return back()->with('error', 'Payment requires additional confirmation. Please try again.');
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during quantity update', [
                'user_id' => $user->id,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $this->friendlyStripeError($e));
        }
    }

    public function updatePaymentMethod(UpdatePaymentMethodRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->billingService->updatePaymentMethod(
                $user,
                $request->validated('payment_method'),
            );

            $this->auditService->log(AnalyticsEvent::BILLING_PAYMENT_METHOD_UPDATED, [
                'user_id' => $user->id,
            ]);

            $this->cacheManager->invalidateBilling();

            return redirect()->route('billing.index')->with('success', 'Payment method updated successfully.');
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during payment method update', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $this->friendlyStripeError($e));
        }
    }

    public function portal(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing('subscriptions.items');

        if (! $user->subscribed('default') && ! $user->hasStripeId()) {
            return back()->with('error', 'No billing account found.');
        }

        try {
            $url = $this->billingService->getBillingPortalUrl(
                $user,
                route('billing.index'),
            );

            return redirect()->away($url);
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during billing portal access', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to access the billing portal. Please try again later.');
        }
    }

    private function invalidateAdminCaches(): void
    {
        $this->cacheManager->invalidateBilling();
    }

    private function friendlyStripeError(ApiErrorException $e): string
    {
        $code = $e->getStripeCode();

        return match ($code) {
            'card_declined' => 'Your card was declined. Please try a different payment method.',
            'expired_card' => 'Your card has expired. Please update your payment method.',
            'processing_error' => 'There was an error processing your card. Please try again in a few minutes.',
            'incorrect_cvc' => 'The CVC number is incorrect. Please check and try again.',
            'insufficient_funds' => 'Insufficient funds. Please try a different payment method.',
            default => 'Unable to process your request. Please try again or contact support.',
        };
    }
}
