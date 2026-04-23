<?php

namespace App\Services\Billing\Stats;

use App\Services\BillingService;
use Illuminate\Support\Facades\DB;

class MrrCalculator
{
    public function __construct(
        private BillingService $billingService,
    ) {}

    public function calculate(): float
    {
        $grouped = DB::table('subscriptions')
            ->join('subscription_items', 'subscriptions.id', '=', 'subscription_items.subscription_id')
            ->where('subscriptions.stripe_status', 'active')
            ->whereNull('subscriptions.ends_at')
            ->select('subscription_items.stripe_price', DB::raw('SUM(COALESCE(subscriptions.quantity, 1)) as total_quantity'))
            ->groupBy('subscription_items.stripe_price')
            ->get();

        $mrr = 0;
        foreach ($grouped as $row) {
            $tier = $this->billingService->resolveTierFromPrice($row->stripe_price);

            if ($tier === null) {
                // Skip unknown prices — they're already logged by resolveTierFromPrice
                continue;
            }

            $monthlyPrice = (float) config("plans.{$tier->value}.price_monthly", 0);

            $annualPriceId = config("plans.{$tier->value}.stripe_price_annual");
            if ($row->stripe_price === $annualPriceId) {
                $monthlyPrice = (float) config("plans.{$tier->value}.price_annual", 0) / 12;
            }

            $mrr += $monthlyPrice * (int) $row->total_quantity;
        }

        return round($mrr, 2);
    }
}
