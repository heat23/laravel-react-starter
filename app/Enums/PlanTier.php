<?php

namespace App\Enums;

/**
 * Subscription plan tiers — single source of truth for tier identity.
 *
 * String values match `config('plans.tier_hierarchy')` and the persisted
 * values in the database, so PlanTier::from($dbString) always round-trips.
 *
 * Unknown/null persisted values should be treated as PlanTier::Free by callers.
 */
enum PlanTier: string
{
    case Free = 'free';
    case Pro = 'pro';
    case ProTeam = 'pro_team';
    case Team = 'team';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Pro => 'Pro',
            self::ProTeam => 'Pro Team',
            self::Team => 'Team',
            self::Enterprise => 'Enterprise',
        };
    }

    /** Ordinal position in the tier hierarchy (0 = lowest). */
    public function rank(): int
    {
        return match ($this) {
            self::Free => 0,
            self::Pro => 1,
            self::ProTeam => 2,
            self::Team => 3,
            self::Enterprise => 4,
        };
    }

    public function canUpgradeTo(self $target): bool
    {
        return $target->rank() > $this->rank();
    }

    /**
     * Resolve a PlanTier from a Stripe price ID, returning null for unknown prices.
     * Iterates paid tiers in descending order (same logic as BillingService::resolveTierFromPrice).
     */
    public static function tryFromStripePriceId(string $priceId): ?self
    {
        if ($priceId === '') {
            return null;
        }

        $paidTiers = array_reverse(array_filter(
            config('plans.tier_hierarchy', []),
            fn (string $t) => $t !== 'free'
        ));

        foreach ($paidTiers as $tier) {
            $monthly = config("plans.{$tier}.stripe_price_monthly");
            $annual = config("plans.{$tier}.stripe_price_annual");

            if (($monthly && $priceId === $monthly) || ($annual && $priceId === $annual)) {
                return self::from($tier);
            }
        }

        return null;
    }

    /**
     * Return the string value of a nullable tier, or a fallback string for null.
     * Avoids ?->value ?? fallback patterns that trigger PHPStan nullsafe.neverNull.
     */
    public static function safeValue(?self $tier, string $fallback = 'unknown'): string
    {
        return $tier !== null ? $tier->value : $fallback;
    }

    /**
     * Resolve a PlanTier from a Stripe price ID, throwing on unknown prices.
     *
     * @throws \InvalidArgumentException
     */
    public static function fromStripePriceId(string $priceId): self
    {
        $tier = self::tryFromStripePriceId($priceId);

        if ($tier === null) {
            throw new \InvalidArgumentException("Unknown Stripe price ID: {$priceId}");
        }

        return $tier;
    }
}
