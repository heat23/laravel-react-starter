<?php

use App\Models\User;
use App\Services\CustomerHealthService;
use App\Services\EngagementScoringService;
use App\Services\LeadScoringService;
use Illuminate\Support\Facades\DB;

afterEach(fn () => Mockery::close());

it('composite score is clamped to 100 when sub-services return out-of-range values', function () {
    $user = new User;
    $user->id = 999999; // Unlikely to exist; getPqlSignal returns 0

    // Stub both services to return values above 0-100 range.
    // Pre-clamp: (150*0.5)+(120*0.3)+(0*0.2) = 75+36 = 111 → must clamp to 100.
    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('score')->once()->with($user)->andReturn(150);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('calculateHealthScore')->once()->with($user)->andReturn(120);

    DB::shouldReceive('table')->once()->with('audit_logs')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('exists')->once()->andReturn(false);

    $service = new LeadScoringService($engagementMock, $healthMock);

    expect($service->score($user))->toBe(100);
});

it('returns composite score formula result when under cap with pql signal', function () {
    $user = new User;
    $user->id = 999998;

    // (60*0.5)+(50*0.3)+(20*0.2) = 30+15+4 = 49
    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('score')->once()->with($user)->andReturn(60);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('calculateHealthScore')->once()->with($user)->andReturn(50);

    DB::shouldReceive('table')->once()->with('audit_logs')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('exists')->once()->andReturn(true);

    $service = new LeadScoringService($engagementMock, $healthMock);

    expect($service->score($user))->toBe(49);
});

it('returns composite score formula result when under cap without pql signal', function () {
    $user = new User;
    $user->id = 999997;

    // (60*0.5)+(50*0.3)+(0*0.2) = 30+15 = 45
    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('score')->once()->with($user)->andReturn(60);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('calculateHealthScore')->once()->with($user)->andReturn(50);

    DB::shouldReceive('table')->once()->with('audit_logs')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('exists')->once()->andReturn(false);

    $service = new LeadScoringService($engagementMock, $healthMock);

    expect($service->score($user))->toBe(45);
});

it('exposes mql and sql thresholds', function () {
    $service = app(LeadScoringService::class);

    expect($service->getMqlThreshold())->toBe(60)
        ->and($service->getSqlThreshold())->toBe(80);
});

// ── Threshold-crossing boundary tests ─────────────────────────────────────
// Expected values computed independently from the formula, not copied from it.

it('produces a score of exactly 60 (MQL boundary) when inputs round up to threshold', function () {
    // Manually: (100 × 0.5) + (33 × 0.3) + (0 × 0.2) = 50.0 + 9.9 + 0.0 = 59.9 → round = 60
    $user = new User;
    $user->id = 999996;

    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('score')->once()->with($user)->andReturn(100);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('calculateHealthScore')->once()->with($user)->andReturn(33);

    DB::shouldReceive('table')->once()->with('audit_logs')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('exists')->once()->andReturn(false);

    $service = new LeadScoringService($engagementMock, $healthMock);

    expect($service->score($user))->toBe(60); // at MQL threshold
});

it('produces a score of 59 (just below MQL) when inputs fall short of threshold', function () {
    // Manually: (100 × 0.5) + (30 × 0.3) + (0 × 0.2) = 50.0 + 9.0 + 0.0 = 59.0 → round = 59
    $user = new User;
    $user->id = 999995;

    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('score')->once()->with($user)->andReturn(100);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('calculateHealthScore')->once()->with($user)->andReturn(30);

    DB::shouldReceive('table')->once()->with('audit_logs')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('exists')->once()->andReturn(false);

    $service = new LeadScoringService($engagementMock, $healthMock);

    expect($service->score($user))->toBe(59); // one point below MQL threshold
});

it('produces a score of exactly 80 (SQL boundary) when inputs equal threshold', function () {
    // Manually: (100 × 0.5) + (100 × 0.3) + (0 × 0.2) = 50.0 + 30.0 + 0.0 = 80.0 → round = 80
    $user = new User;
    $user->id = 999994;

    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('score')->once()->with($user)->andReturn(100);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('calculateHealthScore')->once()->with($user)->andReturn(100);

    DB::shouldReceive('table')->once()->with('audit_logs')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('exists')->once()->andReturn(false);

    $service = new LeadScoringService($engagementMock, $healthMock);

    expect($service->score($user))->toBe(80); // at SQL threshold
});

it('produces a score of 79 (just below SQL) when inputs fall short of SQL threshold', function () {
    // Manually: (100 × 0.5) + (96 × 0.3) + (0 × 0.2) = 50.0 + 28.8 + 0.0 = 78.8 → round = 79
    $user = new User;
    $user->id = 999993;

    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('score')->once()->with($user)->andReturn(100);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('calculateHealthScore')->once()->with($user)->andReturn(96);

    DB::shouldReceive('table')->once()->with('audit_logs')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('exists')->once()->andReturn(false);

    $service = new LeadScoringService($engagementMock, $healthMock);

    expect($service->score($user))->toBe(79); // one point below SQL threshold
});

it('PQL signal lifts a sub-MQL score across the MQL threshold', function () {
    // Without PQL: (78 × 0.5) + (60 × 0.3) + (0 × 0.2) = 39.0 + 18.0 + 0.0 = 57 (below MQL=60)
    // With PQL:    (78 × 0.5) + (60 × 0.3) + (20 × 0.2) = 39.0 + 18.0 + 4.0 = 61 (above MQL=60)
    $user = new User;
    $user->id = 999992;

    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('score')->twice()->with($user)->andReturn(78);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('calculateHealthScore')->twice()->with($user)->andReturn(60);

    // First call: no PQL → score = 57 (below MQL)
    DB::shouldReceive('table')->twice()->with('audit_logs')->andReturnSelf();
    DB::shouldReceive('where')->andReturnSelf();
    DB::shouldReceive('exists')->twice()->andReturn(false, true);

    $service = new LeadScoringService($engagementMock, $healthMock);

    expect($service->score($user))->toBe(57) // below MQL without PQL
        ->and($service->score($user))->toBe(61); // above MQL with PQL signal
});
