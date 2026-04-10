<?php

use App\Models\User;
use App\Services\EngagementScoringService;
use Illuminate\Support\Facades\DB;

it('awards onboarding bonus when value is an ISO timestamp (DASH-003)', function () {
    $user = User::factory()->onboardingIncomplete()->create();

    // Onboarding.tsx stores ISO timestamp as value, not '1'
    DB::table('user_settings')->insert([
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

    DB::table('user_settings')->insert([
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

it('awards base token score but no depth bonus when user has exactly 4 tokens', function () {
    // User with no login, no onboarding, new account → all other score components = 0.
    // featureAdoptionScore(tokens=4): base +10, depth bonus NOT awarded (4 < 5). Expected total = 10.
    $user = User::factory()->onboardingIncomplete()->create(['last_login_at' => null]);
    for ($i = 0; $i < 4; $i++) {
        $user->createToken("token-{$i}");
    }

    $score = app(EngagementScoringService::class)->score($user);

    expect($score)->toBe(10);
});

it('awards depth bonus when user has exactly 5 tokens', function () {
    // featureAdoptionScore(tokens=5): base +10, depth bonus +3 (5 >= 5). Expected total = 13.
    $user = User::factory()->onboardingIncomplete()->create(['last_login_at' => null]);
    for ($i = 0; $i < 5; $i++) {
        $user->createToken("token-{$i}");
    }

    $score = app(EngagementScoringService::class)->score($user);

    expect($score)->toBe(13);
});
