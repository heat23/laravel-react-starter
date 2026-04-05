<?php

use App\Enums\AdminCacheKey;
use App\Enums\LifecycleStage;
use App\Models\User;
use App\Models\UserStageHistory;
use App\Notifications\WinBackNotification;
use App\Services\LifecycleService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

/**
 * Insert a UserStageHistory row with a specific created_at.
 * UserStageHistory::$fillable excludes created_at, so we bypass mass-assignment
 * via a direct DB insert to set custom timestamps in tests.
 *
 * @param  array<string, mixed>  $attributes
 */
function insertStageHistory(array $attributes): void
{
    DB::table('user_stage_history')->insert(array_merge([
        'created_at' => now(),
    ], $attributes));
}

beforeEach(function () {
    Queue::fake();
    Notification::fake();
    $this->service = app(LifecycleService::class);
});

// ──────────────────────────────────────────────────────────────
// transition()
// ──────────────────────────────────────────────────────────────

it('transitions a user to a new stage and records history', function () {
    $user = User::factory()->create(['lifecycle_stage' => LifecycleStage::TRIAL->value]);

    $this->service->transition($user, LifecycleStage::ACTIVATED, 'completed_onboarding');

    expect($user->fresh()->lifecycle_stage)->toBe(LifecycleStage::ACTIVATED->value);

    $history = UserStageHistory::where('user_id', $user->id)->latest()->first();
    expect($history)->not->toBeNull()
        ->and($history->from_stage)->toBe(LifecycleStage::TRIAL->value)
        ->and($history->to_stage)->toBe(LifecycleStage::ACTIVATED->value)
        ->and($history->reason)->toBe('completed_onboarding');
});

it('is a no-op when user is already in the target stage', function () {
    $user = User::factory()->create(['lifecycle_stage' => LifecycleStage::TRIAL->value]);

    $this->service->transition($user, LifecycleStage::TRIAL, 'repeat');

    expect(UserStageHistory::where('user_id', $user->id)->count())->toBe(0);
});

it('records null from_stage for a user with no previous stage', function () {
    $user = User::factory()->create(['lifecycle_stage' => null]);

    $this->service->transition($user, LifecycleStage::TRIAL, 'signed_up');

    $history = UserStageHistory::where('user_id', $user->id)->first();
    expect($history->from_stage)->toBeNull()
        ->and($history->to_stage)->toBe(LifecycleStage::TRIAL->value);
});

it('stores days_in_previous_stage in history metadata when prior transition exists', function () {
    $user = User::factory()->create(['lifecycle_stage' => LifecycleStage::TRIAL->value]);

    // Seed an earlier transition record 10 days ago so we can measure duration
    insertStageHistory([
        'user_id' => $user->id,
        'from_stage' => LifecycleStage::VISITOR->value,
        'to_stage' => LifecycleStage::TRIAL->value,
        'reason' => 'initial',
        'created_at' => now()->subDays(10)->toDateTimeString(),
    ]);

    $this->service->transition($user, LifecycleStage::ACTIVATED, 'active');

    $history = UserStageHistory::where('user_id', $user->id)
        ->where('to_stage', LifecycleStage::ACTIVATED->value)
        ->first();

    expect($history->metadata)->toHaveKey('days_in_previous_stage');
    expect($history->metadata['days_in_previous_stage'])->toBeGreaterThanOrEqual(9);
});

it('dispatches WinBackNotification when user transitions to CHURNED', function () {
    $user = User::factory()->create(['lifecycle_stage' => LifecycleStage::AT_RISK->value]);

    $this->service->transition($user, LifecycleStage::CHURNED, 'subscription_cancelled');

    Notification::assertSentTo($user, WinBackNotification::class);
});

it('does NOT dispatch WinBackNotification for non-churn transitions', function () {
    $user = User::factory()->create(['lifecycle_stage' => LifecycleStage::TRIAL->value]);

    $this->service->transition($user, LifecycleStage::ACTIVATED, 'onboarded');

    Notification::assertNothingSent();
});

it('invalidates the stage funnel cache on transition', function () {
    $cacheKey = AdminCacheKey::STAGE_FUNNEL->value;
    Cache::put($cacheKey, ['some' => 'data'], 300);

    $user = User::factory()->create(['lifecycle_stage' => LifecycleStage::TRIAL->value]);
    $this->service->transition($user, LifecycleStage::ACTIVATED, 'test');

    expect(Cache::has($cacheKey))->toBeFalse();
});

// ──────────────────────────────────────────────────────────────
// getStageVelocity() — median computation
// ──────────────────────────────────────────────────────────────

it('returns median days (not mean) for stage pairs — odd count', function () {
    // Three users: 1, 3, 20 days in trial. Mean = 8, Median = 3.
    // curr = transition from trial→activated at T; prev = transition to trial at T - $d days
    $days = [1, 3, 20];
    foreach ($days as $d) {
        $user = User::factory()->create();
        $currAt = now()->subDays(5)->toDateTimeString();
        $prevAt = now()->subDays(5 + $d)->toDateTimeString();

        insertStageHistory([
            'user_id' => $user->id,
            'from_stage' => LifecycleStage::TRIAL->value,
            'to_stage' => LifecycleStage::ACTIVATED->value,
            'reason' => 'test',
            'created_at' => $currAt,
        ]);
        insertStageHistory([
            'user_id' => $user->id,
            'from_stage' => LifecycleStage::VISITOR->value,
            'to_stage' => LifecycleStage::TRIAL->value,
            'reason' => 'test',
            'created_at' => $prevAt,
        ]);
    }

    Cache::forget(AdminCacheKey::STAGE_VELOCITY->value);

    $result = $this->service->getStageVelocity();

    $key = LifecycleStage::TRIAL->value.'_to_'.LifecycleStage::ACTIVATED->value;
    expect($result)->toHaveKey($key);
    // Median of [1,3,20] = 3, not 8 (mean)
    expect($result[$key])->toBe(3.0);
});

it('returns median days for stage pairs — even count', function () {
    // Four users: 1, 3, 5, 21 days. Mean = 7.5, Median = (3+5)/2 = 4.0
    $days = [1, 3, 5, 21];
    foreach ($days as $d) {
        $user = User::factory()->create();
        $currAt = now()->subDays(5)->toDateTimeString();
        $prevAt = now()->subDays(5 + $d)->toDateTimeString();

        insertStageHistory([
            'user_id' => $user->id,
            'from_stage' => LifecycleStage::ACTIVATED->value,
            'to_stage' => LifecycleStage::PAYING->value,
            'reason' => 'test',
            'created_at' => $currAt,
        ]);
        insertStageHistory([
            'user_id' => $user->id,
            'from_stage' => LifecycleStage::TRIAL->value,
            'to_stage' => LifecycleStage::ACTIVATED->value,
            'reason' => 'test',
            'created_at' => $prevAt,
        ]);
    }

    Cache::forget(AdminCacheKey::STAGE_VELOCITY->value);

    $result = $this->service->getStageVelocity();

    $key = LifecycleStage::ACTIVATED->value.'_to_'.LifecycleStage::PAYING->value;
    expect($result)->toHaveKey($key);
    // Median of [1,3,5,21] = (3+5)/2 = 4.0; mean would be 7.5
    expect($result[$key])->toBe(4.0);
});

it('excludes transitions older than 90 days from velocity calculation', function () {
    $user = User::factory()->create();
    // Old transition pair (91+ days ago) — should be excluded from the window
    insertStageHistory([
        'user_id' => $user->id,
        'from_stage' => LifecycleStage::TRIAL->value,
        'to_stage' => LifecycleStage::ACTIVATED->value,
        'reason' => 'old',
        'created_at' => now()->subDays(91)->toDateTimeString(),
    ]);
    insertStageHistory([
        'user_id' => $user->id,
        'from_stage' => LifecycleStage::VISITOR->value,
        'to_stage' => LifecycleStage::TRIAL->value,
        'reason' => 'old',
        'created_at' => now()->subDays(95)->toDateTimeString(),
    ]);

    Cache::forget(AdminCacheKey::STAGE_VELOCITY->value);

    $result = $this->service->getStageVelocity();

    $key = LifecycleStage::TRIAL->value.'_to_'.LifecycleStage::ACTIVATED->value;
    expect($result)->not->toHaveKey($key);
});

it('caches velocity results', function () {
    Cache::forget(AdminCacheKey::STAGE_VELOCITY->value);

    $this->service->getStageVelocity();

    expect(Cache::has(AdminCacheKey::STAGE_VELOCITY->value))->toBeTrue();
});
