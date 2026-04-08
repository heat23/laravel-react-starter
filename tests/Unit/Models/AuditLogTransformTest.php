<?php

use App\Models\AuditLog;
use App\Models\User;

it('transforms audit log to detail array with all fields', function () {
    $user = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
    $log = AuditLog::factory()->create([
        'event' => 'user.login',
        'user_id' => $user->id,
        'ip' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'metadata' => ['browser' => 'Chrome'],
    ]);
    $log->load('user');

    $result = $log->toDetailArray();

    expect($result)->toHaveKeys(['id', 'event', 'user_name', 'user_email', 'user_id', 'ip', 'user_agent', 'metadata', 'created_at'])
        ->and($result['id'])->toBe($log->id)
        ->and($result['event'])->toBe('user.login')
        ->and($result['user_name'])->toBe('Jane Doe')
        ->and($result['user_email'])->toBe('jane@example.com')
        ->and($result['user_id'])->toBe($user->id)
        ->and($result['ip'])->toBe('192.168.1.1')
        ->and($result['user_agent'])->toBe('Mozilla/5.0')
        ->and($result['metadata'])->toBe(['browser' => 'Chrome'])
        ->and($result['created_at'])->toBe($log->created_at->toISOString());
});

it('transforms audit log to summary array with common fields', function () {
    $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    $log = AuditLog::factory()->create([
        'event' => 'user.logout',
        'user_id' => $user->id,
        'ip' => '10.0.0.1',
    ]);
    $log->load('user');

    $result = $log->toSummaryArray();

    expect($result)->toHaveKeys(['id', 'event', 'user_name', 'user_email', 'ip', 'created_at'])
        ->and($result)->not->toHaveKeys(['user_agent', 'metadata', 'user_id'])
        ->and($result['id'])->toBe($log->id)
        ->and($result['event'])->toBe('user.logout')
        ->and($result['user_name'])->toBe('John Doe')
        ->and($result['user_email'])->toBe('john@example.com')
        ->and($result['ip'])->toBe('10.0.0.1')
        ->and($result['created_at'])->toBe($log->created_at->toISOString());
});

it('handles null user in detail array', function () {
    $log = AuditLog::factory()->create([
        'event' => 'system.cron',
        'user_id' => null,
    ]);
    $log->load('user');

    $result = $log->toDetailArray();

    expect($result['user_name'])->toBeNull()
        ->and($result['user_email'])->toBeNull()
        ->and($result['user_id'])->toBeNull();
});

it('handles null user in summary array', function () {
    $log = AuditLog::factory()->create([
        'event' => 'system.cron',
        'user_id' => null,
    ]);
    $log->load('user');

    $result = $log->toSummaryArray();

    expect($result['user_name'])->toBeNull()
        ->and($result['user_email'])->toBeNull();
});

// ============================================
// Query scopes
// ============================================

it('scopeByUser filters audit logs to the given user id', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    AuditLog::factory()->create(['user_id' => $user->id, 'event' => 'auth.login']);
    AuditLog::factory()->create(['user_id' => $user->id, 'event' => 'profile.updated']);
    AuditLog::factory()->create(['user_id' => $other->id, 'event' => 'auth.login']);

    $results = AuditLog::byUser($user->id)->get();

    expect($results)->toHaveCount(2);
    $results->each(fn ($log) => expect($log->user_id)->toBe($user->id));
});

it('scopeByUser returns empty collection for user with no logs', function () {
    $user = User::factory()->create();

    expect(AuditLog::byUser($user->id)->get())->toHaveCount(0);
});

it('scopeByEvent filters audit logs to the given event name', function () {
    AuditLog::factory()->create(['event' => 'auth.login']);
    AuditLog::factory()->create(['event' => 'auth.login']);
    AuditLog::factory()->create(['event' => 'profile.updated']);

    $results = AuditLog::byEvent('auth.login')->get();

    expect($results)->toHaveCount(2);
    $results->each(fn ($log) => expect($log->event)->toBe('auth.login'));
});

it('scopeByEvent returns empty collection for non-existent event', function () {
    AuditLog::factory()->create(['event' => 'auth.login']);

    expect(AuditLog::byEvent('nonexistent.event')->get())->toHaveCount(0);
});

it('scopeRecent filters audit logs to within the default 30 days', function () {
    AuditLog::factory()->create(['created_at' => now()->subDays(10)]);
    AuditLog::factory()->create(['created_at' => now()->subDays(29)]);
    AuditLog::factory()->create(['created_at' => now()->subDays(31)]);

    $results = AuditLog::recent()->get();

    // 2 logs within 30 days, 1 outside
    expect($results)->toHaveCount(2);
});

it('scopeRecent includes a log created exactly 30 days ago', function () {
    AuditLog::factory()->create(['created_at' => now()->subDays(30)]);
    AuditLog::factory()->create(['created_at' => now()->subDays(31)]);

    $results = AuditLog::recent()->get();

    // scopeRecent uses >= so the boundary day (exactly 30 days ago) is included
    expect($results)->toHaveCount(1);
});

it('scopeRecent respects a custom days parameter', function () {
    AuditLog::factory()->create(['created_at' => now()->subDays(3)]);
    AuditLog::factory()->create(['created_at' => now()->subDays(8)]);
    AuditLog::factory()->create(['created_at' => now()->subDays(15)]);

    $results = AuditLog::recent(7)->get();

    // Only logs within the last 7 days: subDays(3) qualifies, subDays(8) does not
    // The scope uses >= so a log at exactly subDays(7) would also be included
    expect($results)->toHaveCount(1);
});
