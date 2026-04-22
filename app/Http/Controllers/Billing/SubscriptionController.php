<?php

namespace App\Http\Controllers\Billing;

use App\Enums\AuditEvent;
use App\Enums\PlanTier;
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
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private AuditService $auditService,
        private CacheInvalidationManager $cacheManager,
        private PlanLimitService $planLimitService,
    ) {}

    /**
     * Create a Stripe Checkout session for new subscribers and redirect to Stripe's hosted page.
     * For existing subscribers, use swap() instead.
     */
    public function checkout(SubscribeRequest $request): Response
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

        $tierConfig = config("plans.{$tier->value}");
        if (! empty($tierConfig['coming_soon'] ?? false) || config('features.billing.coming_soon', false)) {
            return back()->with('error', 'This plan is coming soon and not yet available for purchase.');
        }

        $seatError = $this->billingService->validateSeatCount($tier, $quantity);
        if ($seatError) {
            return back()->with('error', $seatError);
        }

        try {
            $checkoutUrl = $this->billingService->createCheckoutSession(
                $user,
                $priceId,
                $quantity,
                route('billing.index', ['checkout' => 'success', 'plan' => $tier->value]),
                route('pricing'),
                $request->validated('coupon'),
            );

            return Inertia::location($checkoutUrl);
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during checkout session creation', [
                'user_id' => $user->id,
                'price_id' => $priceId,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $this->friendlyStripeError($e));
        }
    }

    /**
     * Direct Stripe API subscription creation (legacy path).
     *
     * @deprecated Use checkout() instead. The checkout() method redirects to Stripe's hosted
     *             checkout page, which handles PCI compliance, SCA/3DS, and payment method
     *             collection. This method requires a pre-collected payment method token and
     *             is retained for backward compatibility only.
     *
     * Canonical flow: POST /billing/checkout → Stripe Hosted Checkout → billing.index
     * Legacy flow:    POST /billing/subscribe → direct Stripe API → billing.index
     */
    public function subscribe(SubscribeRequest $request): RedirectResponse
    {
        if (! config('billing.legacy_subscribe_enabled', true)) {
            abort(410, 'This endpoint has been retired. Use POST /billing/checkout instead.');
        }

        Log::warning('billing.subscribe legacy endpoint called — use checkout() instead', [
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ]);

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

        $tierConfig = config("plans.{$tier->value}");
        if (! empty($tierConfig['coming_soon'] ?? false) || config('features.billing.coming_soon', false)) {
            return back()->with('error', 'This plan is coming soon and not yet available for purchase.');
        }

        $seatError = $this->billingService->validateSeatCount($tier, $quantity);
        if ($seatError) {
            return back()->with('error', $seatError);
        }

        try {
            $wasOnTrial = $this->planLimitService->isOnTrial($user);
            $trialEndsAt = $user->trial_ends_at;

            $this->billingService->createSubscription(
                $user,
                $priceId,
                $request->validated('payment_method'),
                $request->validated('coupon'),
                $quantity,
            );

            $tierConfig = config("plans.{$tier->value}");
            $amount = (float) ($tierConfig['price_monthly'] ?? 0) * $quantity;
            $isVariantPrice = ($tierConfig['stripe_price_monthly_variant'] ?? null) !== null
                && $priceId === $tierConfig['stripe_price_monthly_variant'];

            $this->auditService->log(AuditEvent::SUBSCRIPTION_CREATED, array_filter([
                'user_id' => $user->id,
                'price_id' => $priceId,
                'tier' => $tier,
                'quantity' => $quantity,
                'amount' => $amount,
                'ab_variant' => $isVariantPrice ? 'pro_monthly_variant' : null,
            ], fn ($v) => $v !== null));

            // Emit TRIAL_CONVERTED if the user was on our local trial when they subscribed.
            // Clear trial_ends_at so CheckExpiredTrials never fires TRIAL_EXPIRED for them.
            if ($wasOnTrial) {
                $user->update(['trial_ends_at' => null]);
                $this->planLimitService->invalidateUserPlanCache($user);

                $this->auditService->log(AuditEvent::TRIAL_CONVERTED, [
                    'user_id' => $user->id,
                    'tier' => $tier,
                    'trial_ends_at' => $trialEndsAt?->toISOString(),
                ]);
            }

            // Log trial start if the new Cashier subscription is in Stripe trialing state
            $user->loadMissing('subscriptions');
            $newSubscription = $user->subscription('default');
            if ($newSubscription && $newSubscription->onTrial()) {
                $trialDays = config('plans.trial.days', 14);
                $this->auditService->log(AuditEvent::TRIAL_STARTED, [
                    'user_id' => $user->id,
                    'tier' => $tier,
                    'trial_days' => $trialDays,
                    'trial_ends_at' => $newSubscription->trial_ends_at?->toISOString(),
                ]);
            }

            $this->planLimitService->invalidateUserPlanCache($user);
            $this->invalidateAdminCaches();

            return redirect()->route('billing.index', ['checkout' => 'success', 'plan' => $tier->value])->with('success', 'Subscription created successfully.');
        } catch (ConcurrentOperationException) {
            return back()->with('error', 'A subscription request is already in progress. Please try again.');
        } catch (IncompletePayment $e) {
            // Subscription created in incomplete state (3DS/SCA required).
            // Redirect to Cashier's hosted payment confirmation page for the user to authenticate.
            return redirect()->route('cashier.payment', ['id' => $e->payment->id]);
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

            $this->auditService->log(AuditEvent::SUBSCRIPTION_CANCELED, array_filter([
                'user_id' => $user->id,
                'immediately' => $immediately,
                'reason' => $request->validated('reason'),
                'feedback' => $request->validated('feedback'),
                'churn_type' => 'voluntary',
            ], fn ($v) => $v !== null));

            $this->planLimitService->invalidateUserPlanCache($user);
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

            // Mark that this resume was user-initiated so the webhook handler skips
            // its own dispatch (preventing a double-count in analytics).
            Cache::put("billing.resume_analytics_sent:{$user->id}", true, now()->addSeconds(90));

            $this->auditService->log(AuditEvent::SUBSCRIPTION_RESUMED, [
                'user_id' => $user->id,
            ]);

            $this->planLimitService->invalidateUserPlanCache($user);
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

    /**
     * Preview proration cost for a plan swap using Stripe's invoice preview API.
     * Returns amount_due (in cents) and next_billing_date so the UI can show exact charges.
     */
    public function swapPreview(Request $request): JsonResponse
    {
        $request->validate(['price_id' => ['required', 'string']]);

        $user = $request->user();
        $user->loadMissing('subscriptions.items');

        if (! $user->subscribed('default')) {
            return response()->json(['message' => 'No active subscription.'], 400);
        }

        $subscription = $user->subscription('default');
        if (! $subscription->items->first()) {
            return response()->json(['message' => 'Subscription item not found.'], 400);
        }

        try {
            $preview = $this->billingService->previewSwapProration($user, $request->input('price_id'));

            return response()->json($preview);
        } catch (ApiErrorException $e) {
            Log::warning('Stripe proration preview failed', [
                'user_id' => $user->id,
                'price_id' => $request->input('price_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Unable to fetch proration preview.'], 500);
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
        $coupon = $request->validated('coupon');

        try {
            $this->billingService->swapPlan($user, $newPriceId, $coupon);

            $newTier = $this->billingService->resolveTierFromPrice($newPriceId);

            $this->auditService->log(AuditEvent::SUBSCRIPTION_SWAPPED, [
                'user_id' => $user->id,
                'new_price_id' => $newPriceId,
                'new_tier' => PlanTier::safeValue($newTier),
            ]);

            $this->planLimitService->invalidateUserPlanCache($user);
            $this->invalidateAdminCaches();

            return redirect()->route('billing.index', ['checkout' => 'success', 'plan' => PlanTier::safeValue($newTier), 'swapped' => 'true'])->with('success', 'Plan updated successfully.');
        } catch (ConcurrentOperationException) {
            return back()->with('error', 'A plan change is already in progress. Please try again.');
        } catch (IncompletePayment $e) {
            // Subscription requires 3DS/SCA — redirect to Cashier's hosted payment confirmation page.
            return redirect()->route('cashier.payment', ['id' => $e->payment->id]);
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

            $this->auditService->log(AuditEvent::SUBSCRIPTION_QUANTITY_UPDATED, [
                'user_id' => $user->id,
                'quantity' => $quantity,
            ]);

            $this->planLimitService->invalidateUserPlanCache($user);
            $this->invalidateAdminCaches();

            return redirect()->route('billing.index')->with('success', 'Seat count updated successfully.');
        } catch (ConcurrentOperationException) {
            return back()->with('error', 'A quantity update is already in progress. Please try again.');
        } catch (IncompletePayment $e) {
            // Subscription requires 3DS/SCA — redirect to Cashier's hosted payment confirmation page.
            return redirect()->route('cashier.payment', ['id' => $e->payment->id])
                ->with('info', 'Additional payment authentication is required to update your seat count.');
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

            $this->auditService->log(AuditEvent::BILLING_PAYMENT_METHOD_UPDATED, [
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

    public function applyRetentionCoupon(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $couponId = config('plans.retention_coupon_id');

        if (! $couponId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Retention coupon is not configured.'], 422);
            }

            return back()->with('error', 'Retention coupon is not configured.');
        }

        try {
            $this->billingService->applyRetentionCoupon($user, $couponId);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Discount applied! Your 20% discount has been added to your subscription.']);
            }

            return back()->with('success', 'Discount applied successfully.');
        } catch (ConcurrentOperationException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'A discount request is already in progress. Please try again.'], 409);
            }

            return back()->with('error', 'A discount request is already in progress. Please try again.');
        } catch (\DomainException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 400);
            }

            return back()->with('error', $e->getMessage());
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error applying retention coupon', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unable to apply discount. Please contact support.'], 500);
            }

            return back()->with('error', 'Unable to apply discount. Please try again or contact support.');
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
