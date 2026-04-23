<?php

use App\Models\User;
use App\Services\Billing\Stats\GrowthChartCalculator;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('returns array of date/count maps for last 30 days', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $calculator = app(GrowthChartCalculator::class);
    $result = $calculator->calculate();

    expect($result)->toBeArray();
    if (count($result) > 0) {
        expect($result[0])->toHaveKeys(['date', 'count']);
        expect($result[0]['count'])->toBeInt();
    }
});
