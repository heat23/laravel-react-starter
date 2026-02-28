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
