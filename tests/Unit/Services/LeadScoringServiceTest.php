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
    DB::shouldReceive('where')->times(3)->andReturnSelf();
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
    DB::shouldReceive('where')->times(3)->andReturnSelf();
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
    DB::shouldReceive('where')->times(3)->andReturnSelf();
    DB::shouldReceive('exists')->once()->andReturn(false);

    $service = new LeadScoringService($engagementMock, $healthMock);

    expect($service->score($user))->toBe(45);
});

it('exposes mql and sql thresholds', function () {
    $service = app(LeadScoringService::class);

    expect($service->getMqlThreshold())->toBe(60)
        ->and($service->getSqlThreshold())->toBe(80);
});
