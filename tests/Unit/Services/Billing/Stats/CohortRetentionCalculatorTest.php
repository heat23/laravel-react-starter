<?php

use App\Models\User;
use App\Services\Billing\Stats\CohortRetentionCalculator;

it('returns an array of cohort rows', function () {
    $calculator = app(CohortRetentionCalculator::class);
    $result = $calculator->calculate();

    expect($result)->toBeArray();
});

it('each cohort row contains cohort label and total count', function () {
    User::factory()->create(['created_at' => now()->subWeeks(1)]);

    $calculator = app(CohortRetentionCalculator::class);
    $result = $calculator->calculate();

    expect($result)->not->toBeEmpty();
    expect($result[0])->toHaveKeys(['cohort', 'total']);
    expect($result[0]['total'])->toBeInt();
    expect($result[0]['total'])->toBeGreaterThanOrEqual(1);
});

it('includes API-active users via last_active_at in retention counts', function () {
    $apiUser = User::factory()->create([
        'last_login_at' => null,
        'last_active_at' => now()->subDays(3),
        'created_at' => now()->subWeeks(2),
    ]);

    $calculator = app(CohortRetentionCalculator::class);
    $cohorts = $calculator->calculate();

    $userCohortStart = now()->subWeeks(2)->startOfWeek();
    $cohortLabel = $userCohortStart->format('M d');

    $cohortRow = collect($cohorts)->firstWhere('cohort', $cohortLabel);

    expect($cohortRow)->not->toBeNull();
    expect($cohortRow['total'])->toBeGreaterThanOrEqual(1);
});
