<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    registerAdminRoutes();
});

it('redirects guests to login', function () {
    $this->get('/admin/audit-logs')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/audit-logs')->assertStatus(403);
});

it('returns 403 for non-admin export', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/audit-logs/export')->assertStatus(403);
});

it('loads index with audit logs', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create([
        'event' => 'auth.login',
        'user_id' => $admin->id,
        'ip' => '127.0.0.1',
        'metadata' => ['email' => $admin->email],
    ]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/AuditLogs/Index')
        ->has('logs.data', 1)
        ->has('eventTypes')
    );
});

it('filters by event type', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    AuditLog::create(['event' => 'auth.logout', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs?event=auth.login');

    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 1)
        ->where('logs.data.0.event', 'auth.login')
    );
});

it('filters by date range', function () {
    $admin = User::factory()->admin()->create();

    $oldLog = AuditLog::create(['event' => 'old.event', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    $oldLog->forceFill(['created_at' => now()->subDays(60)])->saveQuietly();

    AuditLog::create(['event' => 'recent.event', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    $from = now()->subDays(7)->format('Y-m-d');
    $to = now()->format('Y-m-d');

    $response = $this->actingAs($admin)->get("/admin/audit-logs?from={$from}&to={$to}");

    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 1)
        ->where('logs.data.0.event', 'recent.event')
    );
});

it('shows log detail with metadata', function () {
    $admin = User::factory()->admin()->create();
    $log = AuditLog::create([
        'event' => 'auth.login',
        'user_id' => $admin->id,
        'ip' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0',
        'metadata' => ['email' => $admin->email],
    ]);

    $response = $this->actingAs($admin)->get("/admin/audit-logs/{$log->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/AuditLogs/Show')
        ->where('auditLog.id', $log->id)
        ->where('auditLog.event', 'auth.login')
        ->has('auditLog.metadata')
    );
});

it('exports audit logs as CSV', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs/export');

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

it('sanitizes CSV export against formula injection', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => '=cmd|test', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs/export');

    $content = $response->streamedContent();
    expect($content)->toContain("'=cmd|test");
    expect($content)->not->toContain('"=cmd|test"');
});

it('filters by user_id', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    AuditLog::create(['event' => 'auth.login', 'user_id' => $user->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get("/admin/audit-logs?user_id={$user->id}");

    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 1)
        ->where('logs.data.0.user_id', $user->id)
    );
});

it('filters by soft-deleted user_id', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $user->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    $user->delete();

    $response = $this->actingAs($admin)->get("/admin/audit-logs?user_id={$user->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 1)
        ->where('logs.data.0.user_id', $user->id)
    );
});

it('exports by soft-deleted user_id', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $user->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    AuditLog::create(['event' => 'auth.logout', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    $user->delete();

    $response = $this->actingAs($admin)->get("/admin/audit-logs/export?user_id={$user->id}");

    $response->assertStatus(200);
    $content = $response->streamedContent();
    expect($content)->toContain('auth.login');
    expect($content)->not->toContain('auth.logout');
});

it('accepts non-existent user_id without validation error', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/audit-logs?user_id=99999');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 0)
    );
});

it('returns empty list when no logs exist', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/audit-logs');

    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 0)
        ->has('eventTypes', 0)
    );
});

it('shows log detail with user info', function () {
    $admin = User::factory()->admin()->create();
    $log = AuditLog::create([
        'event' => 'auth.login',
        'user_id' => $admin->id,
        'ip' => '192.168.1.1',
        'user_agent' => 'Test Agent',
        'metadata' => ['key' => 'value'],
    ]);

    $response = $this->actingAs($admin)->get("/admin/audit-logs/{$log->id}");

    $response->assertInertia(fn ($page) => $page
        ->where('auditLog.user_name', $admin->name)
        ->where('auditLog.user_email', $admin->email)
        ->where('auditLog.ip', '192.168.1.1')
        ->where('auditLog.user_agent', 'Test Agent')
    );
});

it('shows system event without user', function () {
    $admin = User::factory()->admin()->create();
    $log = AuditLog::create([
        'event' => 'system.event',
        'user_id' => null,
        'ip' => null,
        'metadata' => [],
    ]);

    $response = $this->actingAs($admin)->get("/admin/audit-logs/{$log->id}");

    $response->assertInertia(fn ($page) => $page
        ->where('auditLog.user_name', null)
        ->where('auditLog.user_email', null)
        ->where('auditLog.ip', null)
    );
});

it('returns 404 for non-existent audit log', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/audit-logs/99999')->assertStatus(404);
});

it('exports audit logs in newest-first order', function () {
    $admin = User::factory()->admin()->create();

    $old = AuditLog::create(['event' => 'first.event', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    $new = AuditLog::create(['event' => 'second.event', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs/export');

    $content = $response->streamedContent();
    $firstPos = strpos($content, 'second.event');
    $secondPos = strpos($content, 'first.event');
    expect($firstPos)->toBeLessThan($secondPos, 'Newer events should appear before older events in export');
});

it('exports with event filter', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create(['event' => 'auth.login', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    AuditLog::create(['event' => 'auth.logout', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs/export?event=auth.login');

    $content = $response->streamedContent();
    expect($content)->toContain('auth.login');
    expect($content)->not->toContain('auth.logout');
});

it('returns filters in response', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/audit-logs?event=auth.login&from=2026-01-01&to=2026-12-31');

    $response->assertInertia(fn ($page) => $page
        ->where('filters.event', 'auth.login')
        ->where('filters.from', '2026-01-01')
        ->where('filters.to', '2026-12-31')
    );
});

it('paginates audit logs', function () {
    $admin = User::factory()->admin()->create();
    for ($i = 0; $i < 60; $i++) {
        AuditLog::create(['event' => 'test.event', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    }

    $response = $this->actingAs($admin)->get('/admin/audit-logs');

    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 50)
        ->where('logs.total', 60)
        ->where('logs.last_page', 2)
    );
});

it('audit log index query count does not scale with log count', function () {
    $admin = User::factory()->admin()->create();
    for ($i = 0; $i < 20; $i++) {
        AuditLog::create(['event' => 'test.event', 'user_id' => $admin->id, 'ip' => '127.0.0.1', 'metadata' => []]);
    }

    DB::enableQueryLog();
    $this->actingAs($admin)->get('/admin/audit-logs');
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Should be constant: auth + paginated logs with eager-loaded user + event types cache
    expect($queryCount)->toBeLessThan(15);
});
