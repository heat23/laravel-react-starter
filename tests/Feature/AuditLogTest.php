<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;

test('audit log model scopes', function () {
    $user = User::factory()->create();

    AuditLog::factory()->create(['event' => 'auth.login', 'user_id' => $user->id]);
    AuditLog::factory()->create(['event' => 'auth.logout', 'user_id' => $user->id]);
    AuditLog::factory()->create(['event' => 'auth.login', 'user_id' => null]);

    $this->assertCount(2, AuditLog::byUser($user->id)->get());
    $this->assertCount(2, AuditLog::byEvent('auth.login')->get());
    $this->assertCount(3, AuditLog::recent(30)->get());
});

test('audit log metadata is cast to array', function () {
    $log = AuditLog::factory()->create(['metadata' => ['key' => 'value']]);

    $this->assertIsArray($log->fresh()->metadata);
    $this->assertEquals('value', $log->fresh()->metadata['key']);
});

test('audit service persists login to database', function () {
    $user = User::factory()->create();

    $service = new AuditService;
    $service->logLogin($user);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'auth.login',
        'user_id' => $user->id,
    ]);

    $log = AuditLog::first();
    $this->assertEquals($user->email, $log->metadata['email']);
});

test('audit service persists logout to database', function () {
    $user = User::factory()->create();

    $service = new AuditService;
    $service->logLogout($user);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'auth.logout',
        'user_id' => $user->id,
    ]);
});

test('audit service persists registration to database', function () {
    $user = User::factory()->create(['signup_source' => 'github']);

    $service = new AuditService;
    $service->logRegistration($user);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'auth.register',
        'user_id' => $user->id,
    ]);

    $log = AuditLog::first();
    $this->assertEquals('github', $log->metadata['signup_source']);
});

test('audit service generic log', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $service = new AuditService;
    $service->log('settings.updated', ['setting' => 'theme', 'value' => 'dark']);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'settings.updated',
        'user_id' => $user->id,
    ]);

    $log = AuditLog::first();
    $this->assertEquals('theme', $log->metadata['setting']);
});

test('recent scope excludes old records', function () {
    AuditLog::factory()->create([
        'created_at' => now()->subDays(60),
    ]);

    AuditLog::factory()->create([
        'created_at' => now()->subDays(10),
    ]);

    $this->assertCount(1, AuditLog::recent(30)->get());
});

test('audit log survives user deletion', function () {
    $user = User::factory()->create();
    AuditLog::factory()->create(['user_id' => $user->id, 'event' => 'auth.login']);

    $user->forceDelete();

    $log = AuditLog::first();
    $this->assertNotNull($log);
    $this->assertNull($log->user_id);
});
