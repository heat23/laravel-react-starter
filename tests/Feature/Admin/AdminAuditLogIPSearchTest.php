<?php

use App\Models\AuditLog;
use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('filters audit logs by IP address', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '192.0.2.1', 'metadata' => []]);
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '198.51.100.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs?ip=192.0.2.1');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 1)
        ->where('logs.data.0.ip', '192.0.2.1')
    );
});

it('does not return logs with different IP when IP filter is active', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '192.0.2.1', 'metadata' => []]);
    AuditLog::create(['event' => 'auth.logout', 'user_id' => $admin->id, 'ip' => '10.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs?ip=192.0.2.1');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('logs.data', 1));
});

it('searches audit logs by event name', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    AuditLog::create(['event' => 'billing.subscribed', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs?search=billing');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 1)
        ->where('logs.data.0.event', 'billing.subscribed')
    );
});

it('searches audit logs by metadata content', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create([
        'event' => 'auth.login',
        'user_id' => $admin->id,
        'ip' => '127.0.0.1',
        'metadata' => ['subscription_id' => 'sub_abc123'],
    ]);
    AuditLog::create([
        'event' => 'auth.logout',
        'user_id' => $admin->id,
        'ip' => '127.0.0.1',
        'metadata' => ['email' => 'test@example.com'],
    ]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs?search=subscription_id');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('logs.data', 1));
});

it('rejects IP filter longer than 45 characters', function () {
    $admin = User::factory()->admin()->create();
    $longIp = str_repeat('a', 46);

    // Use getJson to trigger JSON validation response (422) rather than web redirect
    $response = $this->actingAs($admin)->getJson("/admin/audit-logs?ip={$longIp}");

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['ip']);
});

it('rejects search filter longer than 255 characters', function () {
    $admin = User::factory()->admin()->create();
    $longSearch = str_repeat('x', 256);

    // Use getJson to trigger JSON validation response (422) rather than web redirect
    $response = $this->actingAs($admin)->getJson("/admin/audit-logs?search={$longSearch}");

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['search']);
});

it('returns ip and search in filters response', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/audit-logs?ip=192.0.2.1&search=login');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('filters.ip', '192.0.2.1')
        ->where('filters.search', 'login')
    );
});

it('CSV export respects IP filter', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '192.0.2.1', 'metadata' => []]);
    AuditLog::create(['event' => 'auth.logout', 'user_id' => $admin->id, 'ip' => '10.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs/export?ip=192.0.2.1');

    $response->assertOk();
    $content = $response->streamedContent();
    expect($content)->toContain('auth.login');
    expect($content)->not->toContain('auth.logout');
});

it('CSV export respects search filter', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    AuditLog::create(['event' => 'billing.subscribed', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs/export?search=billing');

    $response->assertOk();
    $content = $response->streamedContent();
    expect($content)->toContain('billing.subscribed');
    expect($content)->not->toContain('auth.login');
});

it('combining IP and search filters narrows results', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '192.0.2.1', 'metadata' => []]);
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '10.0.0.1', 'metadata' => []]);
    AuditLog::create(['event' => 'billing.subscribed', 'user_id' => $admin->id, 'ip' => '192.0.2.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs?ip=192.0.2.1&search=auth');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('logs.data', 1));
});

it('escapes LIKE wildcards in audit log search', function () {
    $admin = User::factory()->admin()->create();

    AuditLog::create(['event' => 'test%event.fired', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    AuditLog::create(['event' => 'testGeneral.fired', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    // Literal % in search should match only the log with event "test%event.fired"
    $response = $this->actingAs($admin)->get('/admin/audit-logs?search=test%25event');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('logs.data', 1)
            ->where('logs.data.0.event', 'test%event.fired')
        );
});
