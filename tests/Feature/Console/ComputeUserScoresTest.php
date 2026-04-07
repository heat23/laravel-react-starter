<?php

use App\Models\User;
use App\Services\CustomerHealthService;
use App\Services\EngagementScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('computes and persists health and engagement scores for active users', function () {
    $user = User::factory()->create();

    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('scoreBatch')->andReturn([$user->id => 75]);
    $this->app->instance(EngagementScoringService::class, $engagementMock);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('primeHealthScoreCaches')->once();
    $healthMock->shouldReceive('calculateHealthScore')->andReturn(80);
    $this->app->instance(CustomerHealthService::class, $healthMock);

    $this->artisan('users:compute-scores')
        ->expectsOutputToContain('Computed scores for 1')
        ->assertExitCode(0);

    $updated = DB::table('users')->where('id', $user->id)->first();
    expect($updated->health_score)->toBe(80)
        ->and($updated->engagement_score)->toBe(75)
        ->and($updated->scores_computed_at)->not->toBeNull();
});

it('skips soft-deleted users', function () {
    // The User model uses SoftDeletes, so the global `whereNull(deleted_at)` scope
    // automatically excludes soft-deleted rows from all queries. The command does NOT
    // add its own whereNull clause — this test verifies the global scope is in effect.
    $user = User::factory()->create(['deleted_at' => now()]);

    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('scoreBatch')->andReturn([]);
    $this->app->instance(EngagementScoringService::class, $engagementMock);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('primeHealthScoreCaches')->zeroOrMoreTimes();
    $healthMock->shouldReceive('calculateHealthScore')->never();
    $this->app->instance(CustomerHealthService::class, $healthMock);

    $this->artisan('users:compute-scores')
        ->expectsOutputToContain('Computed scores for 0')
        ->assertExitCode(0);
});

it('continues processing remaining users when one user throws an exception', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $callCount = 0;
    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('scoreBatch')->andReturn([$user1->id => 50, $user2->id => 60]);
    $this->app->instance(EngagementScoringService::class, $engagementMock);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('primeHealthScoreCaches')->once();
    $healthMock->shouldReceive('calculateHealthScore')->andReturnUsing(function (User $u) use ($user1) {
        if ($u->id === $user1->id) {
            throw new RuntimeException('score failure');
        }

        return 70;
    });
    $this->app->instance(CustomerHealthService::class, $healthMock);

    $this->artisan('users:compute-scores')
        ->expectsOutputToContain('Computed scores for 1')
        ->assertExitCode(0);

    // user2 should still be updated despite user1 failing
    $updated = DB::table('users')->where('id', $user2->id)->first();
    expect($updated->health_score)->toBe(70);
});

it('outputs the count of processed users', function () {
    User::factory()->count(3)->create();

    $engagementMock = Mockery::mock(EngagementScoringService::class);
    $engagementMock->shouldReceive('scoreBatch')->andReturn([]);
    $this->app->instance(EngagementScoringService::class, $engagementMock);

    $healthMock = Mockery::mock(CustomerHealthService::class);
    $healthMock->shouldReceive('primeHealthScoreCaches');
    $healthMock->shouldReceive('calculateHealthScore')->andReturn(50);
    $this->app->instance(CustomerHealthService::class, $healthMock);

    $this->artisan('users:compute-scores')
        ->expectsOutputToContain('Computed scores for 3')
        ->assertExitCode(0);
});
