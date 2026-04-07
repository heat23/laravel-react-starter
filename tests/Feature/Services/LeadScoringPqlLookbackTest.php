<?php

use App\Models\User;
use App\Services\CustomerHealthService;
use App\Services\EngagementScoringService;
use App\Services\LeadScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Feature tests for the PQL 30-day lookback window in LeadScoringService.
 *
 * getPqlSignal() queries audit_logs for 'limit.threshold_80' events within
 * the last 30 days. These tests verify the boundary: events on or within the
 * window count; events older than 30 days do not.
 */
beforeEach(function () {
    // Stub sub-services to return 0 so only the PQL lookback is under test.
    $this->engagementMock = Mockery::mock(EngagementScoringService::class);
    $this->engagementMock->shouldReceive('score')->andReturn(0);

    $this->healthMock = Mockery::mock(CustomerHealthService::class);
    $this->healthMock->shouldReceive('calculateHealthScore')->andReturn(0);

    $this->service = new LeadScoringService($this->engagementMock, $this->healthMock);
});

afterEach(fn () => Mockery::close());

it('includes PQL signal when audit event is within the 30-day lookback window', function () {
    $user = User::factory()->create();

    DB::table('audit_logs')->insert([
        'user_id' => $user->id,
        'event' => 'limit.threshold_80',
        'created_at' => now()->subDays(29), // inside 30-day window
    ]);

    // Manually: (0 × 0.5) + (0 × 0.3) + (20 × 0.2) = 4
    expect($this->service->score($user))->toBe(4);
});

it('includes PQL signal when audit event is exactly 30 days ago (inclusive boundary)', function () {
    $user = User::factory()->create();

    DB::table('audit_logs')->insert([
        'user_id' => $user->id,
        'event' => 'limit.threshold_80',
        'created_at' => now()->subDays(30), // exactly at boundary — must be included
    ]);

    // Manually: (0 × 0.5) + (0 × 0.3) + (20 × 0.2) = 4
    expect($this->service->score($user))->toBe(4);
});

it('excludes PQL signal when audit event is older than 30 days', function () {
    $user = User::factory()->create();

    DB::table('audit_logs')->insert([
        'user_id' => $user->id,
        'event' => 'limit.threshold_80',
        'created_at' => now()->subDays(31), // outside 30-day window
    ]);

    // Manually: (0 × 0.5) + (0 × 0.3) + (0 × 0.2) = 0
    expect($this->service->score($user))->toBe(0);
});

it('returns 0 PQL signal when no audit event exists for the user', function () {
    $user = User::factory()->create();

    // No audit_logs rows for this user.
    // Manually: (0 × 0.5) + (0 × 0.3) + (0 × 0.2) = 0
    expect($this->service->score($user))->toBe(0);
});

it('ignores other event types in the audit log for PQL signal', function () {
    $user = User::factory()->create();

    DB::table('audit_logs')->insert([
        'user_id' => $user->id,
        'event' => 'limit.threshold_50', // different event — should not trigger PQL
        'created_at' => now()->subDays(1),
    ]);

    expect($this->service->score($user))->toBe(0);
});
