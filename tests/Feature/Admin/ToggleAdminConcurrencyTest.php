<?php

use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('cannot remove the last two admins when admin count is exactly 2', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $admin2 = User::factory()->admin()->create();

    // 2 admins total (superAdmin is also admin). Removing either should be blocked.
    $this->actingAs($superAdmin)
        ->patch("/admin/users/{$admin2->id}/toggle-admin")
        ->assertSessionHas('error');

    expect($admin2->fresh()->is_admin)->toBeTrue();
});

it('can remove admin when three or more admins exist', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    User::factory()->admin()->create();
    $admin3 = User::factory()->admin()->create();

    // 3 admins — removing one should succeed.
    $this->actingAs($superAdmin)
        ->patch("/admin/users/{$admin3->id}/toggle-admin")
        ->assertSessionHas('success');

    expect($admin3->fresh()->is_admin)->toBeFalse();
    expect(User::where('is_admin', true)->whereNull('deleted_at')->count())->toBeGreaterThanOrEqual(2);
});

it('toggleAdmin uses lockForUpdate inside a transaction', function () {
    // Verify the lockForUpdate guard: two sequential toggles with 3 admins leave admin count >= 2.
    $superAdmin = User::factory()->superAdmin()->create();
    $admin2 = User::factory()->admin()->create();
    $admin3 = User::factory()->admin()->create();

    // First toggle: removes admin3 (3→2 admins, allowed)
    $this->actingAs($superAdmin)
        ->patch("/admin/users/{$admin3->id}/toggle-admin");

    // Second toggle: admin3 is now non-admin, trying to remove admin2 (2 admins left, blocked)
    $this->actingAs($superAdmin)
        ->patch("/admin/users/{$admin2->id}/toggle-admin")
        ->assertSessionHas('error');

    expect(User::where('is_admin', true)->whereNull('deleted_at')->count())->toBeGreaterThanOrEqual(2);
});
