<?php

namespace App\Http\Controllers\Billing;

use App\Exceptions\ConcurrentOperationException;
use App\Http\Controllers\Controller;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class RetentionController extends Controller
{
    public function __construct(
        private BillingService $billingService,
    ) {}

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
}
