<?php

use App\Jobs\PersistAuditLog;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('creates only one audit_log row when the same idempotency key is used twice', function () {
    $user = User::factory()->create();
    $key = hash('sha256', 'test-event|'.$user->id.'|'.now()->startOfMinute()->timestamp.'|dedup');

    $job = new PersistAuditLog(
        event: 'test.event',
        userId: $user->id,
        ip: null,
        userAgent: null,
        metadata: ['source' => 'test'],
        idempotencyKey: $key,
    );

    // First dispatch
    $job->handle();
    expect(AuditLog::count())->toBe(1);

    // Second dispatch with identical key — must be swallowed, not throw
    $job->handle();
    expect(AuditLog::count())->toBe(1);
});

it('creates two rows when two different idempotency keys are used', function () {
    $user = User::factory()->create();
    $keyA = hash('sha256', 'test.event|'.$user->id.'|'.now()->startOfMinute()->timestamp.'|unique-a');
    $keyB = hash('sha256', 'test.event|'.$user->id.'|'.now()->startOfMinute()->timestamp.'|unique-b');

    $jobA = new PersistAuditLog('test.event', $user->id, null, null, null, $keyA);
    $jobB = new PersistAuditLog('test.event', $user->id, null, null, null, $keyB);

    $jobA->handle();
    $jobB->handle();

    expect(AuditLog::count())->toBe(2);
});

it('logs at info level and does not throw on duplicate idempotency key', function () {
    Log::spy();

    $user = User::factory()->create();
    $key = hash('sha256', 'dedup.log|'.$user->id.'|'.now()->startOfMinute()->timestamp.'|info');

    $job = new PersistAuditLog('dedup.log', $user->id, null, null, null, $key);

    $job->handle(); // First — succeeds
    $job->handle(); // Second — must log INFO and return without throwing

    Log::shouldHaveReceived('info')->once()->with('audit_log_duplicate_skipped', Mockery::any());
});

it('has ShouldBeUnique implemented with correct uniqueId', function () {
    $key = 'arbitrary-key-'.uniqid();

    $job = new PersistAuditLog('some.event', null, null, null, null, $key);

    expect($job)->toBeInstanceOf(ShouldBeUnique::class);
    expect($job->uniqueId())->toBe($key);
    expect($job->uniqueFor)->toBe(3600);
});

it('uniqueId returns empty string when no idempotency key is provided', function () {
    $job = new PersistAuditLog('some.event', null, null, null, null);

    expect($job->uniqueId())->toBe('');
});
