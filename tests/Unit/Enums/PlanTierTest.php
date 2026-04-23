<?php

use App\Enums\PlanTier;

describe('PlanTier::label()', function () {
    it('returns human-readable labels', function () {
        expect(PlanTier::Free->label())->toBe('Free');
        expect(PlanTier::Pro->label())->toBe('Pro');
        expect(PlanTier::ProTeam->label())->toBe('Pro Team');
        expect(PlanTier::Team->label())->toBe('Team');
        expect(PlanTier::Enterprise->label())->toBe('Enterprise');
    });
});

describe('PlanTier::rank()', function () {
    it('returns ascending ranks', function () {
        expect(PlanTier::Free->rank())->toBe(0);
        expect(PlanTier::Pro->rank())->toBe(1);
        expect(PlanTier::ProTeam->rank())->toBe(2);
        expect(PlanTier::Team->rank())->toBe(3);
        expect(PlanTier::Enterprise->rank())->toBe(4);
    });

    it('ranks are strictly ordered', function () {
        $tiers = PlanTier::cases();
        for ($i = 0; $i < count($tiers) - 1; $i++) {
            expect($tiers[$i]->rank())->toBeLessThan($tiers[$i + 1]->rank());
        }
    });
});

describe('PlanTier::canUpgradeTo()', function () {
    it('returns true when target has higher rank', function () {
        expect(PlanTier::Free->canUpgradeTo(PlanTier::Pro))->toBeTrue();
        expect(PlanTier::Free->canUpgradeTo(PlanTier::Team))->toBeTrue();
        expect(PlanTier::Free->canUpgradeTo(PlanTier::Enterprise))->toBeTrue();
        expect(PlanTier::Pro->canUpgradeTo(PlanTier::ProTeam))->toBeTrue();
        expect(PlanTier::Pro->canUpgradeTo(PlanTier::Team))->toBeTrue();
        expect(PlanTier::ProTeam->canUpgradeTo(PlanTier::Team))->toBeTrue();
        expect(PlanTier::Team->canUpgradeTo(PlanTier::Enterprise))->toBeTrue();
    });

    it('returns false when target has same or lower rank', function () {
        expect(PlanTier::Free->canUpgradeTo(PlanTier::Free))->toBeFalse();
        expect(PlanTier::Pro->canUpgradeTo(PlanTier::Free))->toBeFalse();
        expect(PlanTier::Team->canUpgradeTo(PlanTier::Pro))->toBeFalse();
        expect(PlanTier::Enterprise->canUpgradeTo(PlanTier::Team))->toBeFalse();
        expect(PlanTier::Enterprise->canUpgradeTo(PlanTier::Enterprise))->toBeFalse();
    });
});

describe('PlanTier::from()', function () {
    it('resolves from raw string values matching config', function () {
        expect(PlanTier::from('free'))->toBe(PlanTier::Free);
        expect(PlanTier::from('pro'))->toBe(PlanTier::Pro);
        expect(PlanTier::from('pro_team'))->toBe(PlanTier::ProTeam);
        expect(PlanTier::from('team'))->toBe(PlanTier::Team);
        expect(PlanTier::from('enterprise'))->toBe(PlanTier::Enterprise);
    });

    it('throws on unknown value', function () {
        expect(fn () => PlanTier::from('unknown'))->toThrow(ValueError::class);
    });
});

describe('PlanTier::tryFromStripePriceId()', function () {
    it('returns null for unknown price ID', function () {
        expect(PlanTier::tryFromStripePriceId('price_unknown_xyz'))->toBeNull();
    });

    it('returns null for empty string', function () {
        expect(PlanTier::tryFromStripePriceId(''))->toBeNull();
    });
});

describe('PlanTier::fromStripePriceId()', function () {
    it('throws InvalidArgumentException for unknown price ID', function () {
        expect(fn () => PlanTier::fromStripePriceId('price_unknown_xyz'))
            ->toThrow(InvalidArgumentException::class);
    });
});

describe('PlanTier value round-trip', function () {
    it('serializes to same strings as tier_hierarchy config', function () {
        $hierarchy = config('plans.tier_hierarchy', []);
        foreach ($hierarchy as $tierString) {
            $tier = PlanTier::from($tierString);
            expect($tier->value)->toBe($tierString);
        }
    });

    it('all config tiers have a corresponding enum case', function () {
        $hierarchy = config('plans.tier_hierarchy', []);
        foreach ($hierarchy as $tierString) {
            expect(PlanTier::tryFrom($tierString))->not->toBeNull();
        }
    });
});
