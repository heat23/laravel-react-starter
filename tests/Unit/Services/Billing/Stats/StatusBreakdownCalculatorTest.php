<?php

use App\Models\User;
use App\Services\Billing\Stats\StatusBreakdownCalculator;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('returns array of status/count maps', function () {
    $user = User::factory()->create();
    createSubscription($user);

    $calculator = app(StatusBreakdownCalculator::class);
    $result = $calculator->calculate();

    expect($result)->toBeArray();
    expect($result)->not->toBeEmpty();
    expect($result[0])->toHaveKeys(['status', 'count']);
    expect($result[0]['count'])->toBeInt();
});
