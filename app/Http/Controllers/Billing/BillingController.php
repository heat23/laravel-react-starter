<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\BillingService;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private PlanLimitService $planLimitService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('subscriptions.items');

        $subscription = $user->subscription('default');
        $subscriptionInfo = null;
        $incompletePayment = null;
        $invoices = [];

        if ($subscription) {
            $tier = $this->billingService->resolveTierFromPrice($subscription->stripe_price ?? '') ?? 'free';
            $tierConfig = config("plans.{$tier}");

            $subscriptionInfo = [
                'name' => $tierConfig['name'] ?? ucfirst($tier),
                'status' => $subscription->stripe_status,
                'priceId' => $subscription->stripe_price,
                'trialEndsAt' => $subscription->trial_ends_at?->toISOString(),
                'endsAt' => $subscription->ends_at?->toISOString(),
                'onGracePeriod' => $subscription->onGracePeriod(),
                'canceled' => $subscription->canceled(),
                'active' => $subscription->active(),
            ];

            if ($subscription->hasIncompletePayment()) {
                $incompletePayment = [
                    'paymentId' => $subscription->latestPayment()?->id,
                    'confirmUrl' => route('billing.index'),
                ];
            }

            try {
                $invoices = collect($user->invoices())
                    ->take(config('pagination.billing.invoices', 12))
                    ->map(fn ($invoice) => [
                        'id' => $invoice->id,
                        'date' => $invoice->date()->toISOString(),
                        'amount' => $invoice->rawTotal(),
                        'status' => $invoice->status,
                        'invoice_pdf' => $this->sanitizeInvoiceUrl($invoice->invoicePdf()),
                    ])
                    ->values()
                    ->all();
            } catch (\Exception $e) {
                Log::warning('Failed to fetch invoices', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                // Show user-friendly error message via flash toast (handled by useFlashToasts hook)
                session()->flash('error', 'Unable to load recent invoices. Please try again later.');
            }
        }

        $platformTrial = null;
        if ($this->planLimitService->isOnTrial($user)) {
            $platformTrial = [
                'endsAt' => $user->trial_ends_at->toISOString(),
                'daysRemaining' => $this->planLimitService->trialDaysRemaining($user),
            ];
        }

        return Inertia::render('Billing/Index', [
            'subscription' => $subscriptionInfo,
            'platformTrial' => $platformTrial,
            'incompletePayment' => $incompletePayment,
            'invoices' => $invoices,
            'graceDays' => config('plans.past_due_grace_days', 7),
        ]);
    }

    private function sanitizeInvoiceUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parsed = parse_url($url);

        if (! $parsed || ($parsed['scheme'] ?? '') !== 'https') {
            return null;
        }

        $host = $parsed['host'] ?? '';
        if (! str_ends_with($host, 'stripe.com')) {
            return null;
        }

        return $url;
    }
}
