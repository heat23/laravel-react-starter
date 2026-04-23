<?php

namespace App\Http\Controllers\Billing;

use App\Enums\AuditEvent;
use App\Enums\PlanTier;
use App\Exceptions\ConcurrentOperationException;
use App\Http\Controllers\Billing\Concerns\HandlesBillingErrors;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CancelSubscriptionRequest;
use App\Http\Requests\Billing\SwapPlanRequest;
use App\Http\Requests\Billing\UpdateQuantityRequest;
use App\Services\AuditService;
use App\Services\BillingService;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Exception\ApiErrorException;

class SubscriptionLifecycleController extends Controller
{
    use HandlesBillingErrors;

    public function __construct(
        private BillingService $billingService,
        private AuditService $auditService,
        private PlanLimitService $planLimitService,
    ) {}

    public function cancel(CancelSubscriptionRequest $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
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
        $subscription = $user->subscription('default');
        if (! $subscription || ! $subscription->onGracePeriod()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No canceled subscription to resume.'], 400);
            }

            return back()->with('error', 'No canceled subscription to resume.');
        }

        try {
            $this->billingService->resumeSubscription($user);

            // Prevents webhook handler from double-dispatching resume analytics.
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

    public function swapPreview(Request $request): JsonResponse
    {
        $request->validate(['price_id' => ['required', 'string']]);

        $user = $request->user();

        if (! $user->subscribed('default')) {
            return response()->json(['message' => 'No active subscription.'], 400);
        }

        if (! $user->subscription('default')->items->first()) {
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

        if (! $user->subscribed('default')) {
            return back()->with('error', 'No active subscription to change.');
        }

        $newPriceId = $request->validated('price_id');
        try {
            $this->billingService->swapPlan($user, $newPriceId, $request->validated('coupon'));
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
}
