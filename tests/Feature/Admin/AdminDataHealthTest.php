<?php

use App\Models\User;
use App\Services\DataHealthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    registerAdminRoutes();
});

it('redirects guests to login', function () {
    $this->get('/admin/data-health')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/data-health')->assertStatus(403);
});

it('shows data health page for admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/data-health');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/DataHealth')
        ->has('checks')
    );
});

it('detects orphaned personal access tokens', function () {
    $admin = User::factory()->admin()->create();

    // Create an orphaned token by inserting directly
    DB::table('personal_access_tokens')->insert([
        'tokenable_type' => 'App\\Models\\User',
        'tokenable_id' => 99999,
        'name' => 'orphaned-token',
        'token' => hash('sha256', 'test-token'),
        'abilities' => '["*"]',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(DataHealthService::class);
    $results = $service->runAllChecks();

    expect($results['orphaned_tokens']['status'])->toBe('warning');
    expect($results['orphaned_tokens']['count'])->toBe(1);
});

it('reports ok when no orphaned tokens exist', function () {
    $admin = User::factory()->admin()->create();

    $service = app(DataHealthService::class);
    $results = $service->runAllChecks();

    expect($results['orphaned_tokens']['status'])->toBe('ok');
    expect($results['orphaned_tokens']['count'])->toBe(0);
});

it('reports ok for audit logs when all users exist', function () {
    $user = User::factory()->create();
    DB::table('audit_logs')->insert([
        'event' => 'test.event',
        'user_id' => $user->id,
        'ip' => '127.0.0.1',
        'metadata' => '{}',
        'created_at' => now(),
    ]);

    $service = app(DataHealthService::class);
    $results = $service->runAllChecks();

    expect($results['orphaned_audit_logs']['status'])->toBe('ok');
    expect($results['orphaned_audit_logs']['count'])->toBe(0);
});

it('detects stale webhook deliveries', function () {
    if (! Schema::hasTable('webhook_deliveries')) {
        $this->markTestSkipped('webhook_deliveries table not available');
    }

    DB::table('webhook_endpoints')->insert([
        'user_id' => User::factory()->create()->id,
        'url' => 'https://example.com/webhook',
        'secret' => 'test-secret',
        'events' => '["*"]',
        'active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('webhook_deliveries')->insert([
        'webhook_endpoint_id' => DB::table('webhook_endpoints')->first()->id,
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'event_type' => 'test.event',
        'payload' => '{}',
        'status' => 'pending',
        'attempts' => 0,
        'created_at' => now()->subDays(8),
        'updated_at' => now()->subDays(8),
    ]);

    $service = app(DataHealthService::class);
    $results = $service->runAllChecks();

    expect($results['stale_webhook_deliveries']['status'])->toBe('warning');
    expect($results['stale_webhook_deliveries']['count'])->toBeGreaterThanOrEqual(1);
});
