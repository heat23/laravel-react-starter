<?php

use App\Models\EmailSendLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

test('prune deletes read notifications older than default retention', function () {
    $user = User::factory()->create();

    // Old read notification — beyond 60-day default
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => now()->subDays(61),
        'created_at' => now()->subDays(65),
    ]);

    // Recent read notification — within retention window
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 2]),
        'read_at' => now()->subDays(14),
        'created_at' => now()->subDays(14),
    ]);

    $this->artisan('prune-read-notifications')
        ->expectsOutputToContain('Pruned 1 read notifications older than 60 days')
        ->assertExitCode(0);

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(1);
});

test('prune preserves unread notifications regardless of age', function () {
    $user = User::factory()->create();

    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => null,
        'created_at' => now()->subDays(90),
    ]);

    $this->artisan('prune-read-notifications')
        ->expectsOutputToContain('Pruned 0 read notifications older than 60 days')
        ->assertExitCode(0);

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(1);
});

test('prune respects custom days option', function () {
    $user = User::factory()->create();

    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => now()->subDays(35),
        'created_at' => now()->subDays(35),
    ]);

    // With --days=30 the 35-day-old record should be deleted
    $this->artisan('prune-read-notifications', ['--days' => 30])
        ->expectsOutputToContain('Pruned 1 read notifications older than 30 days')
        ->assertExitCode(0);

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(0);
});

test('win-back dedup via EmailSendLog is unaffected by notification pruning', function () {
    // MSG-005 regression anchor: SendWinBackEmails uses EmailSendLog for dedup, NOT the
    // notifications table. Pruning notifications must never bypass win-back dedup.
    $user = User::factory()->create();

    // Record win-back email 1 in EmailSendLog (the actual dedup table)
    EmailSendLog::record($user->id, 'win_back', 1);

    // Insert an old read notification that will be pruned (simulates the win-back notification
    // that was sent, then read, and has now aged past the retention window)
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => now()->subDays(61),
        'created_at' => now()->subDays(61),
    ]);

    // Prune removes the notification record
    $this->artisan('prune-read-notifications')
        ->expectsOutputToContain('Pruned 1 read notifications older than 60 days')
        ->assertExitCode(0);

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(0);

    // Dedup record must survive the prune — EmailSendLog is independent of the notifications table
    expect(EmailSendLog::alreadySent($user->id, 'win_back', 1))->toBeTrue();
});

test('prune-read-notifications logs a warning when days is below the recommended minimum', function () {
    // Log::listen() hooks into the logging pipeline rather than mocking the facade,
    // so it correctly intercepts calls via Log::warning(), logger(), or injected
    // LoggerInterface — all three route through the same log manager event dispatcher.
    $captured = [];
    Log::listen(function (object $message) use (&$captured): void {
        $captured[] = $message;
    });

    $this->artisan('prune-read-notifications', ['--days' => 30])
        ->assertExitCode(0);

    $warnings = array_filter($captured, fn ($m) => $m->level === 'warning'
        && str_contains((string) $m->message, 'below recommended minimum')
        && ($m->context['days'] ?? null) === 30
        && ($m->context['min_safe_days'] ?? null) === 60
    );

    expect($warnings)->toHaveCount(1);
});

test('prune still deletes rows when days is below MIN_SAFE_DAYS (warning-only contract)', function () {
    // Regression anchor: --days below MIN_SAFE_DAYS (60) emits a warning but MUST still
    // prune. Exit code 0 is intentional — the command is not aborted. This test verifies
    // that the warning-only path results in actual data deletion so operators who pass
    // an aggressive --days value get the expected behaviour and monitoring can detect
    // the unsafe invocation via the warning log entry.
    $user = User::factory()->create();

    // 35-day-old read notification: below MIN_SAFE_DAYS (60) but above --days=30,
    // so it must be deleted when --days=30 is used.
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => now()->subDays(35),
        'created_at' => now()->subDays(35),
    ]);

    // 25-day-old notification: newer than --days=30, must be preserved.
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 2]),
        'read_at' => now()->subDays(25),
        'created_at' => now()->subDays(25),
    ]);

    $this->artisan('prune-read-notifications', ['--days' => 30])
        ->expectsOutputToContain('Pruned 1 read notifications older than 30 days')
        ->assertExitCode(0); // exit 0 is intentional: below-minimum is warning-only, not an error

    // The older record is pruned; the newer one survives.
    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(1);
});

test('default retention of 60 days preserves win-back notifications during full sequence', function () {
    // Win-back sequence: email 1 at day 3, email 2 at day 14, email 3 at day 30-33.
    // With a 60-day default, emails 1 and 2 remain in notifications table throughout the
    // sequence, preserving in-app notification history for the user.
    $user = User::factory()->create();

    // Email 1: sent at day 3, read at day 3 (33 days ago relative to now at day 33+3)
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 1]),
        'read_at' => now()->subDays(33),
        'created_at' => now()->subDays(33),
    ]);

    // Email 2: sent at day 14, read at day 14 (19 days ago)
    DB::table('notifications')->insert([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\WinBackNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['type' => 'win_back', 'email_number' => 2]),
        'read_at' => now()->subDays(19),
        'created_at' => now()->subDays(19),
    ]);

    // Both notifications are < 60 days old — neither should be pruned
    $this->artisan('prune-read-notifications')
        ->expectsOutputToContain('Pruned 0 read notifications older than 60 days')
        ->assertExitCode(0);

    expect(DB::table('notifications')->where('notifiable_id', $user->id)->count())->toBe(2);
});
