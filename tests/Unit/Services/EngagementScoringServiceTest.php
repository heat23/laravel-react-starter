<?php

use App\Models\User;
use App\Services\EngagementScoringService;

it('awards onboarding bonus when value is an ISO timestamp (DASH-003)', function () {
    $user = User::factory()->onboardingIncomplete()->create();

    // Onboarding.tsx stores ISO timestamp as value, not '1'
    \Illuminate\Support\Facades\DB::table('user_settings')->insert([
        'user_id' => $user->id,
        'key' => 'onboarding_completed',
        'value' => '2026-03-15T10:30:00.000Z',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(EngagementScoringService::class);
    $score = $service->score($user);

    // Onboarding bonus is 25 points. Score must be at least 25.
    expect($score)->toBeGreaterThanOrEqual(25);
});

it('does not award onboarding bonus when setting is absent', function () {
    $user = User::factory()->onboardingIncomplete()->create([
        'last_login_at' => null,
        'created_at' => now(),
    ]);

    $service = app(EngagementScoringService::class);
    $score = $service->score($user);

    // No onboarding, no login recency, brand new account — score should be < 25
    expect($score)->toBeLessThan(25);
});

it('scoreBatch awards onboarding bonus when value is ISO timestamp', function () {
    $user = User::factory()->onboardingIncomplete()->create();

    \Illuminate\Support\Facades\DB::table('user_settings')->insert([
        'user_id' => $user->id,
        'key' => 'onboarding_completed',
        'value' => '2026-03-15T10:30:00.000Z',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(EngagementScoringService::class);
    $scores = $service->scoreBatch(collect([$user]));

    expect($scores[$user->id])->toBeGreaterThanOrEqual(25);
});

it('scoreBatch does not award onboarding bonus for absent setting', function () {
    $user = User::factory()->onboardingIncomplete()->create([
        'last_login_at' => null,
        'created_at' => now(),
    ]);

    $service = app(EngagementScoringService::class);
    $scores = $service->scoreBatch(collect([$user]));

    expect($scores[$user->id])->toBeLessThan(25);
});
