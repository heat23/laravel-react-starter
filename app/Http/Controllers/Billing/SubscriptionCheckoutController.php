<?php

namespace App\Http\Controllers\Billing;

use App\Enums\AuditEvent;
use App\Exceptions\ConcurrentOperationException;
use App\Http\Controllers\Billing\Concerns\HandlesBillingErrors;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\SubscribeRequest;
use App\Services\AuditService;
use App\Services\BillingService;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionCheckoutController extends Controller
{
    use HandlesBillingErrors;

    public function __construct(
        private BillingService $billingService,
        private AuditService $auditService,
        private PlanLimitService $planLimitService,
    ) {}

    /**
     * Create a Stripe Checkout session for new subscribers and redirect to Stripe's hosted page.
     * For existing subscribers, use swap() instead.
     */
    public function checkout(SubscribeRequest $request): Response
    {
        $user = $request->user();

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

    public function portal(Request $request): RedirectResponse
    {
        $user = $request->user();

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
}
