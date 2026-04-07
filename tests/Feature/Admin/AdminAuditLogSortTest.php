<?php

use App\Enums\AnalyticsEvent;
use App\Models\AuditLog;
use App\Models\User;

it('sorts audit logs by event ascending', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::factory()->create(['event' => 'login']);
    AuditLog::factory()->create(['event' => 'admin.user.toggle_active']);

    $response = $this->actingAs($admin)
        ->get('/admin/audit-logs?sort=event&dir=asc');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 2)
        ->where('logs.data.0.event', 'admin.user.toggle_active')
    );
});

it('sorts audit logs by created_at descending by default', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::factory()->create(['created_at' => now()->subDay()]);
    AuditLog::factory()->create(['created_at' => now()]);

    $response = $this->actingAs($admin)->get('/admin/audit-logs');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('logs.data', 2)
    );
});

it('rejects invalid sort column', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/audit-logs?sort=password&dir=asc')
        ->assertSessionHasErrors('sort'); // Validation fails
});

it('rejects invalid sort direction', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/audit-logs?dir=sideways')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['dir']);
});

it('rejects invalid per_page value', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/audit-logs?per_page=500')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['per_page']);
});

it('rejects an event filter value not in the AnalyticsEvent enum', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/audit-logs?event=arbitrary_unknown_event')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['event']);
});

it('accepts a valid event filter value from the AnalyticsEvent enum', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->getJson('/admin/audit-logs?event='.AnalyticsEvent::AUTH_LOGIN->value)
        ->assertOk();
});
