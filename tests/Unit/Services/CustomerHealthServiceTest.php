<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\CustomerHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('returns a score between 0 and 100', function () {
    $user = User::factory()->create();
    $service = new CustomerHealthService;

    $score = $service->calculateHealthScore($user);

    expect($score)->toBeGreaterThanOrEqual(0)
        ->toBeLessThanOrEqual(100);
});

it('gives points for email verification', function () {
    $verified = User::factory()->create();
    $unverified = User::factory()->unverified()->create();
    $service = new CustomerHealthService;

    $verifiedScore = $service->calculateHealthScore($verified);
    $unverifiedScore = $service->calculateHealthScore($unverified);

    expect($verifiedScore)->toBeGreaterThan($unverifiedScore);
});

it('gives points for having a password set', function () {
    $withPassword = User::factory()->create(['password' => bcrypt('password')]);
    $withoutPassword = User::factory()->create(['password' => null]);
    $service = new CustomerHealthService;

    $withScore = $service->calculateHealthScore($withPassword);
    $withoutScore = $service->calculateHealthScore($withoutPassword);

    expect($withScore)->toBeGreaterThan($withoutScore);
});

it('calculates activation rate for recent users', function () {
    // Create verified users (activated)
    User::factory()->count(3)->create([
        'created_at' => now()->subDays(5),
        'email_verified_at' => now()->subDays(4),
    ]);

    // Create unverified users (not activated)
    User::factory()->count(2)->unverified()->create([
        'created_at' => now()->subDays(5),
    ]);

    $service = new CustomerHealthService;
    $rate = $service->getEmailVerificationRate();

    expect($rate)->toBe(60.0);
});

it('returns zero activation rate when no users', function () {
    $service = new CustomerHealthService;
    $rate = $service->getEmailVerificationRate();

    expect($rate)->toBe(0.0);
});

it('returns zero trial conversion rate when no trial users', function () {
    $service = new CustomerHealthService;
    $rate = $service->getTrialConversionRate();

    expect($rate)->toBe(0.0);
});

it('returns health distribution buckets', function () {
    User::factory()->count(3)->create();
    $service = new CustomerHealthService;

    $distribution = $service->getHealthDistribution();

    expect($distribution)
        ->toHaveKey('critical')
        ->toHaveKey('at_risk')
        ->toHaveKey('moderate')
        ->toHaveKey('healthy');

    $total = array_sum($distribution);
    expect($total)->toBe(3);
});

it('calculates health score without N+1 queries when counts are preloaded', function () {
    $user = User::factory()->create();
    $user->loadCount(['settings', 'tokens', 'webhookEndpoints']);

    DB::enableQueryLog();
    $service = new CustomerHealthService;
    $service->calculateHealthScore($user);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Counts already loaded — only 1 audit log count + 1 subscription query expected, no loadCount queries
    $loadCountQueries = collect($queries)->filter(fn ($q) => str_contains($q['query'], 'settings_count') || str_contains($q['query'], 'tokens_count'));
    expect($loadCountQueries)->toHaveCount(0);
    // Total queries bounded: audit_log count + subscriptions = 2
    expect(count($queries))->toBeLessThanOrEqual(2);
});

it('loads health distribution for 50 users with bounded query count', function () {
    User::factory()->count(50)->create();

    Cache::forget('metrics:health_distribution');

    DB::enableQueryLog();
    $service = new CustomerHealthService;
    $service->getHealthDistribution();
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // With withCount at chunk level: expect bounded queries, not 4 per user
    expect(count($queries))->toBeLessThanOrEqual(10);
});

// --- Login frequency score ---
// onboardingIncomplete() prevents the factory's auto-created setting from adding 3 feature-adoption points,
// allowing each test to isolate a single score dimension.

it('gives zero login frequency score when no recent logins', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    $service = new CustomerHealthService;

    // 0 (login) + 0 (feature) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(0);
});

it('gives partial login frequency score for 1-3 logins in last 30 days', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    AuditLog::factory()->create([
        'user_id' => $user->id,
        'event' => 'login',
        'created_at' => now()->subDays(5),
    ]);
    $service = new CustomerHealthService;

    // 10 (login) + 0 (feature) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(10);
});

it('gives higher login frequency score for 4-10 logins in last 30 days', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    for ($i = 0; $i < 4; $i++) {
        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event' => 'login',
            'created_at' => now()->subDays(5),
        ]);
    }
    $service = new CustomerHealthService;

    // 18 (login) + 0 (feature) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(18);
});

it('gives maximum login frequency score for 11+ logins in last 30 days', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    for ($i = 0; $i < 11; $i++) {
        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event' => 'login',
            'created_at' => now()->subDays(5),
        ]);
    }
    $service = new CustomerHealthService;

    // 25 (login) + 0 (feature) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(25);
});

it('does not count logins older than 30 days toward login frequency score', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    // 11 old logins — outside the 30-day window
    for ($i = 0; $i < 11; $i++) {
        AuditLog::factory()->create([
            'user_id' => $user->id,
            'event' => 'login',
            'created_at' => now()->subDays(35),
        ]);
    }
    $service = new CustomerHealthService;

    expect($service->calculateHealthScore($user))->toBe(0);
});

// --- Billing status score ---

it('gives maximum billing score for active subscription', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_active_'.uniqid(),
        'stripe_status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $service = new CustomerHealthService;

    // 0 (login) + 0 (feature) + 25 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(25);
});

it('gives partial billing score for trialing subscription', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_trialing_'.uniqid(),
        'stripe_status' => 'trialing',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $service = new CustomerHealthService;

    // 0 + 0 + 20 + 0
    expect($service->calculateHealthScore($user))->toBe(20);
});

it('gives minimal billing score for past_due subscription', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_pastdue_'.uniqid(),
        'stripe_status' => 'past_due',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $service = new CustomerHealthService;

    // 0 + 0 + 5 + 0
    expect($service->calculateHealthScore($user))->toBe(5);
});

it('gives zero billing score for no subscription and no trial', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null, 'trial_ends_at' => null]);
    $service = new CustomerHealthService;

    expect($service->calculateHealthScore($user))->toBe(0);
});

it('gives trial billing score when user has a future trial_ends_at and no subscription', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create([
        'password' => null,
        'trial_ends_at' => now()->addDays(7),
    ]);
    $service = new CustomerHealthService;

    // 0 + 0 + 20 (trial) + 0
    expect($service->calculateHealthScore($user))->toBe(20);
});

// --- D7 / D30 retention rates ---

it('returns zero d7 retention rate when no cohort users', function () {
    Cache::forget('metrics:retention_d7');
    $service = new CustomerHealthService;

    expect($service->getD7RetentionRate())->toBe(0.0);
});

it('calculates d7 retention rate for users active by day 7', function () {
    Cache::forget('metrics:retention_d7');

    // Cohort: created 8 days ago (within 7–10 day window), verified
    $retainedUser = User::factory()->create([
        'created_at' => now()->subDays(8),
        'email_verified_at' => now()->subDays(7),
        'last_active_at' => now()->subDays(1), // after the day-7 mark (2 days ago)
        'last_login_at' => null,
    ]);
    $notRetainedUser = User::factory()->create([
        'created_at' => now()->subDays(8),
        'email_verified_at' => now()->subDays(7),
        'last_active_at' => now()->subDays(9), // before the day-7 mark
        'last_login_at' => null,
    ]);

    $service = new CustomerHealthService;
    $rate = $service->getD7RetentionRate();

    expect($rate)->toBe(50.0);
});

it('falls back to last_login_at for d7 retention when last_active_at is null', function () {
    Cache::forget('metrics:retention_d7');

    $retainedUser = User::factory()->create([
        'created_at' => now()->subDays(8),
        'email_verified_at' => now()->subDays(7),
        'last_active_at' => null,
        'last_login_at' => now()->subDays(1), // after day-7 mark
    ]);

    $service = new CustomerHealthService;
    $rate = $service->getD7RetentionRate();

    expect($rate)->toBe(100.0);
});

it('returns zero d30 retention rate when no cohort users', function () {
    Cache::forget('metrics:retention_d30');
    $service = new CustomerHealthService;

    expect($service->getD30RetentionRate())->toBe(0.0);
});

it('calculates d30 retention rate for users active by day 30', function () {
    Cache::forget('metrics:retention_d30');

    // Cohort: created 31 days ago (within 30–33 day window), verified
    // Day-30 mark = created_at + 29 days = 31 days ago + 29 days = 2 days ago
    $retainedUser = User::factory()->create([
        'created_at' => now()->subDays(31),
        'email_verified_at' => now()->subDays(30),
        'last_active_at' => now()->subDays(1), // after the day-30 mark
        'last_login_at' => null,
    ]);
    $notRetainedUser = User::factory()->create([
        'created_at' => now()->subDays(31),
        'email_verified_at' => now()->subDays(30),
        'last_active_at' => now()->subDays(32), // before the day-30 mark
        'last_login_at' => null,
    ]);

    $service = new CustomerHealthService;
    $rate = $service->getD30RetentionRate();

    expect($rate)->toBe(50.0);
});

// --- Feature adoption score ---

it('confirms baseline: unverified user with null password and zero counts scores 0', function () {
    // Establish that the unverified()->onboardingIncomplete() factory state with password=null
    // and all counts zeroed produces a total score of 0 — validating the additive assumption
    // used in all feature-adoption tests below.
    // - loginFrequencyScore  = 0: score is derived from AuditLog query (not last_login_at);
    //                              factory users have no AuditLog entries.
    // - featureAdoptionScore = 0: all counts are 0.
    // - billingStatusScore   = 0: no subscription row; trial_ends_at is null or in the past.
    // - profileCompletionScore = 0: unverified → no +10; password=null → no +7; settings=0 → no +8.
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    $user->webhook_endpoints_count = 0;
    $user->tokens_count = 0;
    $user->settings_count = 0;

    expect((new CustomerHealthService)->calculateHealthScore($user))->toBe(0);
});

it('gives webhook adoption score when user has webhook endpoints', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    $user->webhook_endpoints_count = 1;
    $user->tokens_count = 0;
    $user->settings_count = 0;
    $service = new CustomerHealthService;

    // 0 (login) + 12 (webhook) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(12);
});

it('gives token adoption score when user has API tokens', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    $user->webhook_endpoints_count = 0;
    $user->tokens_count = 1;
    $user->settings_count = 0;
    $service = new CustomerHealthService;

    // 0 (login) + 10 (tokens) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(10);
});

it('gives depth bonus when user has 5+ API tokens', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    $user->webhook_endpoints_count = 0;
    $user->tokens_count = 5;
    $user->settings_count = 0;
    $service = new CustomerHealthService;

    // 0 (login) + 10 (tokens) + 3 (depth) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(13);
});

it('does not give depth bonus when user has exactly 4 API tokens', function () {
    // Pins the lower boundary: depth bonus requires tokens_count >= 5, so 4 earns only the base token score.
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    $user->webhook_endpoints_count = 0;
    $user->tokens_count = 4;
    $user->settings_count = 0;
    $service = new CustomerHealthService;

    // 0 (login) + 10 (tokens, base only — no depth bonus because count < 5) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(10);
});

it('gives settings adoption score when user has settings configured', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    $user->webhook_endpoints_count = 0;
    $user->tokens_count = 0;
    $user->settings_count = 1;
    $service = new CustomerHealthService;

    // 0 (login) + 3 (settings) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(3);
});

it('caps feature adoption score at 25 when all features active', function () {
    $user = User::factory()->unverified()->onboardingIncomplete()->create(['password' => null]);
    $user->webhook_endpoints_count = 1;
    $user->tokens_count = 5;
    $user->settings_count = 1;
    $service = new CustomerHealthService;

    // Raw: 12 (webhook) + 10 (tokens) + 3 (depth) + 3 (settings) = 28, capped at 25
    // 0 (login) + 25 (feature, capped) + 0 (billing) + 0 (profile)
    expect($service->calculateHealthScore($user))->toBe(25);
});

// --- Trial conversion rate ---

it('calculates trial conversion rate for users with active subscriptions', function () {
    Cache::forget('metrics:trial_conversion_rate');

    // 2 trial users, 1 converted to active subscription
    $converted = User::factory()->create(['trial_ends_at' => now()->subDays(3)]);
    $notConverted = User::factory()->create(['trial_ends_at' => now()->subDays(3)]);

    DB::table('subscriptions')->insert([
        'user_id' => $converted->id,
        'type' => 'default',
        'stripe_id' => 'sub_converted_'.uniqid(),
        'stripe_status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new CustomerHealthService;
    $rate = $service->getTrialConversionRate();

    // 1 converted / 2 trial users = 50.0%
    expect($rate)->toBe(50.0);
});

it('does not count past_due subscriptions as converted trials', function () {
    Cache::forget('metrics:trial_conversion_rate');

    // Explicit non-converted trial user ensures the denominator is 2, owned entirely by this test.
    // Without this, a denominator of 0 (from a misconfigured query) would return 0.0 from the
    // early-guard and make the assertion pass for the wrong reason.
    User::factory()->create(['trial_ends_at' => now()->subDays(3)]);

    $pastDueUser = User::factory()->create(['trial_ends_at' => now()->subDays(3)]);

    DB::table('subscriptions')->insert([
        'user_id' => $pastDueUser->id,
        'type' => 'default',
        'stripe_id' => 'sub_pastdue_'.uniqid(),
        'stripe_status' => 'past_due',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = new CustomerHealthService;
    $rate = $service->getTrialConversionRate();

    // 2 trial users, 0 converted (past_due does not count) = 0.0%
    // If past_due were incorrectly counted, rate would be 50.0% — this assertion would catch it.
    expect($rate)->toBe(0.0);
});

// --- Profile completion: settings bonus ---

it('gives settings bonus in profile score when user has 2+ settings', function () {
    // Verified user (email_verified_at set), no password (no +7), no billing, no webhooks, no tokens.
    // settings_count = 2 contributes to TWO independent score components:
    //   - featureAdoptionScore:   settings_count > 0  → +3
    //   - profileCompletionScore: settings_count >= 2 → +8 (in addition to verified +10)
    // login=0 | feature=3 | billing=0 | profile=18 → total=21
    $user = User::factory()->create(['password' => null, 'trial_ends_at' => null]);
    $user->webhook_endpoints_count = 0;
    $user->tokens_count = 0;
    $user->settings_count = 2;
    $service = new CustomerHealthService;

    expect($service->calculateHealthScore($user))->toBe(21);
});

it('profile settings bonus contributes exactly 8 points above the settings-present baseline', function () {
    // Delta test that isolates the +8 profile bonus by comparing settings_count=1 vs =2.
    // At settings_count=1: feature +3 (settings>0), profile +10 (verified only; <2 threshold) = 13
    // At settings_count=2: feature +3 (same),       profile +10+8 (verified + settings bonus)  = 21
    // Delta = 8 — confirms the profile bonus is independent of the feature adoption path.
    $service = new CustomerHealthService;

    $oneSettingUser = User::factory()->create(['password' => null, 'trial_ends_at' => null]);
    $oneSettingUser->webhook_endpoints_count = 0;
    $oneSettingUser->tokens_count = 0;
    $oneSettingUser->settings_count = 1;
    // 0 (login) + 3 (feature: settings>0) + 0 (billing) + 10 (profile: verified only) = 13
    $scoreWithOneSetting = $service->calculateHealthScore($oneSettingUser);

    $twoSettingsUser = User::factory()->create(['password' => null, 'trial_ends_at' => null]);
    $twoSettingsUser->webhook_endpoints_count = 0;
    $twoSettingsUser->tokens_count = 0;
    $twoSettingsUser->settings_count = 2;
    // 0 (login) + 3 (feature: settings>0) + 0 (billing) + 18 (profile: verified+settings) = 21
    $scoreWithTwoSettings = $service->calculateHealthScore($twoSettingsUser);

    expect($scoreWithOneSetting)->toBe(13);
    expect($scoreWithTwoSettings)->toBe(21);
    expect($scoreWithTwoSettings - $scoreWithOneSetting)->toBe(8);
});
