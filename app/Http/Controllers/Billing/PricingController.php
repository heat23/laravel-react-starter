<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    public function __construct(
        private PlanLimitService $planLimitService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        // A/B cohort: deterministic per-visitor assignment using user ID or session ID.
        // Cohort 1 sees the variant price when PLAN_PRO_PRICE_MONTHLY_VARIANT is set.
        $cohortSeed = $user ? (string) $user->id : $request->session()->getId();
        $isVariantCohort = (abs(crc32($cohortSeed)) % 2) === 1;

        $tiers = [];

        foreach (config('plans.tier_hierarchy', []) as $tierKey) {
            $tierConfig = config("plans.{$tierKey}");
            if (! $tierConfig) {
                continue;
            }

            $stripeMonthlyPriceId = $tierConfig['stripe_price_monthly'] ?? null;
            $stripeAnnualPriceId = $tierConfig['stripe_price_annual'] ?? null;
            $priceMonthly = $tierConfig['price_monthly'] ?? null;
            $priceAnnual = $tierConfig['price_annual'] ?? null;

            // Apply A/B variant prices for Pro tier when variant is configured and visitor is in cohort.
            // Both the display price and the Stripe price ID must swap atomically — never show a variant
            // display price while billing at the control Stripe price ID, or vice versa.
            if ($tierKey === 'pro' && $isVariantCohort) {
                // Monthly variant: only swap when BOTH the display price and the Stripe price ID are set.
                if (! empty($tierConfig['stripe_price_monthly_variant']) && isset($tierConfig['price_monthly_variant'])) {
                    $priceMonthly = $tierConfig['price_monthly_variant'];
                    $stripeMonthlyPriceId = $tierConfig['stripe_price_monthly_variant'];
                }

                // Annual variant: only swap when BOTH the display price and the Stripe price ID are set.
                // If stripe_price_annual_variant is absent, annual billing stays at the control price and
                // annual display price is unchanged — no partial swap occurs.
                if (! empty($tierConfig['stripe_price_annual_variant']) && isset($tierConfig['price_annual_variant'])) {
                    $priceAnnual = $tierConfig['price_annual_variant'];
                    $stripeAnnualPriceId = $tierConfig['stripe_price_annual_variant'];
                }
            }

            // Enterprise self-serve: when a Stripe price is configured but no price_monthly
            // is set (e.g., STRIPE_PRICE_ENTERPRISE is set but PLAN_ENTERPRISE_PRICE_MONTHLY is
            // not), we still expose the stripe_price_id so the UI can show a self-serve checkout.
            // The price label will show 'Custom pricing' but the checkout button will appear.
            // Operators should set PLAN_ENTERPRISE_PRICE_MONTHLY when enabling self-serve.

            $tiers[$tierKey] = [
                'name' => $tierConfig['name'] ?? ucfirst($tierKey),
                'description' => $tierConfig['description'] ?? '',
                'price' => $priceMonthly,
                'price_annual' => $priceAnnual,
                'stripe_price_id' => $stripeMonthlyPriceId,
                'stripe_price_id_annual' => $stripeAnnualPriceId,
                'self_serve' => $stripeMonthlyPriceId !== null,
                'per_seat' => $tierConfig['per_seat'] ?? false,
                'min_seats' => $tierConfig['min_seats'] ?? null,
                'coming_soon' => $tierConfig['coming_soon'] ?? false,
                'popular' => $tierConfig['popular'] ?? false,
                'limits' => $tierConfig['limits'] ?? [],
                'features' => $tierConfig['features'] ?? [],
            ];
        }

        $currentPlan = null;
        $trial = null;

        if ($user) {
            $currentPlan = $this->planLimitService->getUserPlan($user);

            if ($this->planLimitService->isOnTrial($user)) {
                $trial = [
                    'active' => true,
                    'daysRemaining' => $this->planLimitService->trialDaysRemaining($user),
                    'endsAt' => $user->trial_ends_at->toISOString(),
                ];
            }
        }

        return Inertia::render('Pricing', [
            'tiers' => $tiers,
            'currentPlan' => $currentPlan,
            'trial' => $trial,
            'trialEnabled' => $this->planLimitService->isTrialEnabled(),
            'trialDays' => config('plans.trial.days', 14),
            'contactEmail' => config('mail.from.address', 'hello@example.com'),
        ]);
    }
}
