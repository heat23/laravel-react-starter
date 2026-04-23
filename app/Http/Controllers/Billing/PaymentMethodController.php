<?php

namespace App\Http\Controllers\Billing;

use App\Enums\AuditEvent;
use App\Http\Controllers\Billing\Concerns\HandlesBillingErrors;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\UpdatePaymentMethodRequest;
use App\Services\AuditService;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class PaymentMethodController extends Controller
{
    use HandlesBillingErrors;

    public function __construct(
        private BillingService $billingService,
        private AuditService $auditService,
    ) {}

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

            $this->invalidateAdminCaches();

            return redirect()->route('billing.index')->with('success', 'Payment method updated successfully.');
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error during payment method update', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $this->friendlyStripeError($e));
        }
    }
}
