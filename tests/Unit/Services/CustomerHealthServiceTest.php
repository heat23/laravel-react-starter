<?php

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
    $rate = $service->getActivationRate();

    expect($rate)->toBe(60.0);
});

it('returns zero activation rate when no users', function () {
    $service = new CustomerHealthService;
    $rate = $service->getActivationRate();

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

    // Counts already loaded — only audit log query expected (loginFrequencyScore)
    $countQueries = collect($queries)->filter(fn ($q) => str_contains($q['query'], 'count'));
    expect($countQueries)->toHaveCount(0);
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
