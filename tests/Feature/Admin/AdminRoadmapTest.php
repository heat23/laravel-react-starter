<?php

use App\Enums\AuditEvent;
use App\Models\AuditLog;
use App\Models\RoadmapEntry;
use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

// ── Auth ──────────────────────────────────────────────────────────────────────

it('requires admin to view roadmap index', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get('/admin/roadmap')
        ->assertForbidden();
});

it('admin can view roadmap index', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/roadmap')
        ->assertOk();
});

it('admin can view roadmap create page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get('/admin/roadmap/create')
        ->assertOk();
});

// ── Store ─────────────────────────────────────────────────────────────────────

it('admin can create a roadmap entry', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/roadmap', [
            'title' => 'New Feature Request',
            'description' => 'A detailed description.',
            'status' => 'planned',
            'display_order' => 1,
        ])
        ->assertRedirect('/admin/roadmap');

    expect(RoadmapEntry::where('title', 'New Feature Request')->exists())->toBeTrue();
});

it('store logs an audit event', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/admin/roadmap', [
            'title' => 'Audited Entry',
            'status' => 'planned',
        ]);

    $entry = RoadmapEntry::where('title', 'Audited Entry')->first();
    expect($entry)->not->toBeNull();

    expect(AuditLog::where('event', AuditEvent::ADMIN_ROADMAP_ENTRY_CREATED->value)
        ->whereJsonContains('metadata->entry_id', $entry->id)
        ->exists()
    )->toBeTrue();
});

it('store validates required fields', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson('/admin/roadmap', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'status']);
});

it('store validates status enum', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->postJson('/admin/roadmap', ['title' => 'Test', 'status' => 'invalid'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

// ── Update ────────────────────────────────────────────────────────────────────

it('admin can update a roadmap entry', function () {
    $admin = User::factory()->admin()->create();
    $entry = RoadmapEntry::create([
        'title' => 'Original',
        'slug' => 'original',
        'status' => 'planned',
    ]);

    $this->actingAs($admin)
        ->patch("/admin/roadmap/{$entry->id}", ['status' => 'in_progress'])
        ->assertRedirect();

    expect($entry->fresh()->status)->toBe('in_progress');
});

it('update logs an audit event', function () {
    $admin = User::factory()->admin()->create();
    $entry = RoadmapEntry::create([
        'title' => 'To Update',
        'slug' => 'to-update',
        'status' => 'planned',
    ]);

    $this->actingAs($admin)
        ->patch("/admin/roadmap/{$entry->id}", ['status' => 'completed']);

    expect(AuditLog::where('event', AuditEvent::ADMIN_ROADMAP_ENTRY_UPDATED->value)
        ->whereJsonContains('metadata->entry_id', $entry->id)
        ->exists()
    )->toBeTrue();
});

// ── Destroy ───────────────────────────────────────────────────────────────────

it('super admin can delete a roadmap entry', function () {
    $admin = User::factory()->superAdmin()->create();
    $entry = RoadmapEntry::create([
        'title' => 'To Delete',
        'slug' => 'to-delete',
        'status' => 'planned',
    ]);

    $this->actingAs($admin)
        ->delete("/admin/roadmap/{$entry->id}")
        ->assertRedirect('/admin/roadmap');

    expect(RoadmapEntry::find($entry->id))->toBeNull();
});

it('destroy logs an audit event', function () {
    $admin = User::factory()->superAdmin()->create();
    $entry = RoadmapEntry::create([
        'title' => 'Audit Delete',
        'slug' => 'audit-delete',
        'status' => 'planned',
    ]);
    $entryId = $entry->id;

    $this->actingAs($admin)
        ->delete("/admin/roadmap/{$entry->id}");

    expect(AuditLog::where('event', AuditEvent::ADMIN_ROADMAP_ENTRY_DELETED->value)
        ->whereJsonContains('metadata->entry_id', $entryId)
        ->exists()
    )->toBeTrue();
});

it('non-super-admin cannot delete a roadmap entry', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $entry = RoadmapEntry::create([
        'title' => 'Protected',
        'slug' => 'protected',
        'status' => 'planned',
    ]);

    $this->actingAs($admin)
        ->delete("/admin/roadmap/{$entry->id}")
        ->assertForbidden();
});

// ── Search & Filter ───────────────────────────────────────────────────────────

it('admin can search roadmap entries by title', function () {
    $admin = User::factory()->admin()->create();
    RoadmapEntry::create(['title' => 'Dark Mode Support', 'slug' => 'dark-mode', 'status' => 'planned']);
    RoadmapEntry::create(['title' => 'API Rate Limiting', 'slug' => 'api-rate', 'status' => 'in_progress']);

    $response = $this->actingAs($admin)
        ->get('/admin/roadmap?search=Dark')
        ->assertOk();

    $entries = $response->original->getData()['page']['props']['entries'];
    expect($entries['total'])->toBe(1);
    expect($entries['data'][0]['title'])->toBe('Dark Mode Support');
});

it('admin can filter roadmap entries by status', function () {
    $admin = User::factory()->admin()->create();
    RoadmapEntry::create(['title' => 'Feature A', 'slug' => 'feature-a', 'status' => 'planned']);
    RoadmapEntry::create(['title' => 'Feature B', 'slug' => 'feature-b', 'status' => 'completed']);
    RoadmapEntry::create(['title' => 'Feature C', 'slug' => 'feature-c', 'status' => 'planned']);

    $response = $this->actingAs($admin)
        ->get('/admin/roadmap?status=planned')
        ->assertOk();

    $entries = $response->original->getData()['page']['props']['entries'];
    expect($entries['total'])->toBe(2);
    expect(collect($entries['data'])->pluck('status')->unique()->values()->toArray())->toBe(['planned']);
});

it('index returns paginated entries', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->get('/admin/roadmap')
        ->assertOk();

    $props = $response->original->getData()['page']['props'];
    expect($props)->toHaveKey('entries')
        ->and($props['entries'])->toHaveKey('data')
        ->and($props['entries'])->toHaveKey('total')
        ->and($props['entries'])->toHaveKey('current_page');
});

it('index returns filters prop', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->get('/admin/roadmap?search=test&status=planned')
        ->assertOk();

    $props = $response->original->getData()['page']['props'];
    expect($props['filters']['search'])->toBe('test')
        ->and($props['filters']['status'])->toBe('planned');
});
