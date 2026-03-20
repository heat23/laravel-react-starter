<?php

use App\Models\AuditLog;
use App\Models\User;

it('sorts audit logs by event ascending', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::factory()->create(['event' => 'login']);
    AuditLog::factory()->create(['event' => 'admin.user.toggle_active']);

    $response = $this->actingAs($admin)
        ->get('/admin/audit-logs?sort=event&dir=asc');

    $response->assertOk();
    $logs = $response->inertia()->prop('logs.data');
    expect($logs[0]['event'])->toBeLessThanOrEqual($logs[1]['event']);
});

it('sorts audit logs by created_at descending by default', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::factory()->create(['created_at' => now()->subDay()]);
    AuditLog::factory()->create(['created_at' => now()]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs');

    $response->assertOk();
    $logs = $response->inertia()->prop('logs.data');
    expect(count($logs))->toBeGreaterThanOrEqual(2);
    // Newest first by default
    expect($logs[0]['created_at'])->toBeGreaterThanOrEqual($logs[1]['created_at']);
});

it('rejects invalid sort column', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/audit-logs?sort=password&dir=asc')
        ->assertOk(); // Falls back to default, doesn't 422
});
