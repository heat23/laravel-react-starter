<?php

use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

/*
|--------------------------------------------------------------------------
| Bulk Deactivate
|--------------------------------------------------------------------------
*/

it('bulk deactivates non-admin users', function () {
    $admin = User::factory()->admin()->create();
    $users = User::factory()->count(3)->create();
    $ids = $users->pluck('id')->all();

    $response = $this->actingAs($admin)->post('/admin/users/bulk-deactivate', ['ids' => $ids]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    foreach ($users as $user) {
        $user->refresh();
        expect($user->deleted_at)->not->toBeNull();
    }
});

it('excludes self from bulk deactivation', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/users/bulk-deactivate', [
        'ids' => [$admin->id, $user->id],
    ]);

    $response->assertRedirect();

    $admin->refresh();
    expect($admin->deleted_at)->toBeNull();

    $user->refresh();
    expect($user->deleted_at)->not->toBeNull();
});

it('excludes admin users from bulk deactivation', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/users/bulk-deactivate', [
        'ids' => [$otherAdmin->id, $user->id],
    ]);

    $response->assertRedirect();

    $otherAdmin->refresh();
    expect($otherAdmin->deleted_at)->toBeNull();

    $user->refresh();
    expect($user->deleted_at)->not->toBeNull();
});

it('skips already deactivated users', function () {
    $admin = User::factory()->admin()->create();
    $deactivated = User::factory()->create(['deleted_at' => now()]);
    $active = User::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/users/bulk-deactivate', [
        'ids' => [$deactivated->id, $active->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Deactivated 1 user(s).');
});

it('validates ids are required', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/users/bulk-deactivate', ['ids' => []]);

    $response->assertSessionHasErrors('ids');
});

it('validates max 100 ids', function () {
    $admin = User::factory()->admin()->create();
    $ids = range(1, 101);

    $response = $this->actingAs($admin)->post('/admin/users/bulk-deactivate', ['ids' => $ids]);

    $response->assertSessionHasErrors('ids');
});

it('validates ids must exist in users table', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/users/bulk-deactivate', ['ids' => [99999]]);

    $response->assertSessionHasErrors('ids.0');
});

it('creates audit logs for each deactivated user', function () {
    $admin = User::factory()->admin()->create();
    $users = User::factory()->count(2)->create();
    $ids = $users->pluck('id')->all();

    $this->actingAs($admin)->post('/admin/users/bulk-deactivate', ['ids' => $ids]);

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.user_deactivated',
        'user_id' => $admin->id,
    ]);
});

it('returns zero count when all users are excluded', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/users/bulk-deactivate', [
        'ids' => [$admin->id, $otherAdmin->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Deactivated 0 user(s).');
});

it('blocks non-admin from bulk deactivate', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/users/bulk-deactivate', ['ids' => [1]]);

    $response->assertStatus(403);
});
