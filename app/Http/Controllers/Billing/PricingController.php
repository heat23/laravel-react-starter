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
        $tiers = [];

        foreach (config('plans.tier_hierarchy', []) as $tierKey) {
            $tierConfig = config("plans.{$tierKey}");
            if (! $tierConfig) {
                continue;
            }

            $tiers[$tierKey] = [
                'name' => $tierConfig['name'] ?? ucfirst($tierKey),
                'description' => $tierConfig['description'] ?? '',
                'price' => $tierConfig['price_monthly'] ?? null,
                'price_annual' => $tierConfig['price_annual'] ?? null,
                'stripe_price_id' => $tierConfig['stripe_price_monthly'] ?? null,
                'stripe_price_id_annual' => $tierConfig['stripe_price_annual'] ?? null,
                'per_seat' => $tierConfig['per_seat'] ?? false,
                'min_seats' => $tierConfig['min_seats'] ?? null,
                'coming_soon' => $tierConfig['coming_soon'] ?? false,
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
        ]);
    }
}
