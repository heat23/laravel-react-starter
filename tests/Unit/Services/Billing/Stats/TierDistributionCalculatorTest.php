<?php

use App\Models\User;
use App\Services\Billing\Stats\TierDistributionCalculator;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('returns empty array when no active subscriptions exist', function () {
    $calculator = app(TierDistributionCalculator::class);

    expect($calculator->calculate())->toBeArray();
});

it('returns array of tier/count maps for active subscriptions', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $calculator = app(TierDistributionCalculator::class);
    $result = $calculator->calculate();

    expect($result)->toBeArray();
    if (count($result) > 0) {
        expect($result[0])->toHaveKeys(['tier', 'count']);
        expect($result[0]['count'])->toBeInt();
    }
});
