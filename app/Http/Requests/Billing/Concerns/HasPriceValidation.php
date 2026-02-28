<?php

namespace App\Http\Requests\Billing\Concerns;

trait HasPriceValidation
{
    /**
     * @return array<int, string>
     */
    private function allowedPriceIds(): array
    {
        $paidTiers = collect(config('plans.tier_hierarchy', []))->filter(fn ($t) => $t !== 'free');

        return $paidTiers
            ->flatMap(fn ($tier) => [
                config("plans.{$tier}.stripe_price_monthly"),
                config("plans.{$tier}.stripe_price_annual"),
            ])->filter()->values()->all();
    }
}
