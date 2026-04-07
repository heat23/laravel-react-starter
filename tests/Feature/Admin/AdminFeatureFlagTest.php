<?php

use App\Models\FeatureFlagOverride;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    registerAdminRoutes();
    ensureFeatureFlagOverridesTableExists();
    clearFeatureFlagOverrides();
    Cache::flush();
});

it('redirects guests to login', function () {
    $response = $this->get('/admin/feature-flags');

    $response->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get('/admin/feature-flags');

    $response->assertStatus(403);
});

it('loads index page with all feature flags', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/feature-flags');

    $response->assertStatus(200);
    $response->assertInertia(function ($page) {
        $page->component('Admin/FeatureFlags/Index');
        $page->has('flags');
    });
});

it('enables a global override', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/billing', [
        'enabled' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(FeatureFlagOverride::where('flag', 'billing')->whereNull('user_id')->first())
        ->not->toBeNull()
        ->enabled->toBeTrue();
});

it('disables a global override', function () {
    $admin = User::factory()->superAdmin()->create();

    // First enable it
    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => null, 'enabled' => true]);

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/billing', [
        'enabled' => false,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(FeatureFlagOverride::where('flag', 'billing')->whereNull('user_id')->first())
        ->not->toBeNull()
        ->enabled->toBeFalse();
});

it('removes a global override', function () {
    $admin = User::factory()->superAdmin()->create();

    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => null, 'enabled' => true]);

    $response = $this->actingAs($admin)->delete('/admin/feature-flags/billing');

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(FeatureFlagOverride::where('flag', 'billing')->whereNull('user_id')->first())
        ->toBeNull();
});

it('prevents any override on protected admin flag', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/admin', [
        'enabled' => false,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('flag');
});

it('prevents per-user override on protected admin flag', function () {
    $admin = User::factory()->superAdmin()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/feature-flags/admin/users', [
        'user_id' => $targetUser->id,
        'enabled' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('user_override');
});

it('adds a user override', function () {
    $admin = User::factory()->superAdmin()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/feature-flags/billing/users', [
        'user_id' => $targetUser->id,
        'enabled' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(FeatureFlagOverride::where('flag', 'billing')->where('user_id', $targetUser->id)->first())
        ->not->toBeNull()
        ->enabled->toBeTrue();
});

it('removes a user override', function () {
    $admin = User::factory()->superAdmin()->create();
    $targetUser = User::factory()->create();

    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => $targetUser->id, 'enabled' => true]);

    $response = $this->actingAs($admin)->delete("/admin/feature-flags/billing/users/{$targetUser->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(FeatureFlagOverride::where('flag', 'billing')->where('user_id', $targetUser->id)->first())
        ->toBeNull();
});

it('removes all user overrides for a flag', function () {
    $admin = User::factory()->superAdmin()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => $user1->id, 'enabled' => true]);
    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => $user2->id, 'enabled' => false]);

    $response = $this->actingAs($admin)->delete('/admin/feature-flags/billing/users');

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(FeatureFlagOverride::where('flag', 'billing')->whereNotNull('user_id')->count())
        ->toBe(0);
});

it('returns error for unknown flag name', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/unknown_flag', [
        'enabled' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('flag');
});

it('returns validation error for non-existent user_id', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->post('/admin/feature-flags/billing/users', [
        'user_id' => 99999,
        'enabled' => true,
    ]);

    // Inertia redirects with validation errors in session
    $response->assertSessionHasErrors('user_id');
});

it('audit logs global override changes', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/billing', [
        'enabled' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.feature_flag.global_override',
        'user_id' => $admin->id,
    ]);
});

it('audit logs user override changes', function () {
    $admin = User::factory()->superAdmin()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/feature-flags/billing/users', [
        'user_id' => $targetUser->id,
        'enabled' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.feature_flag.user_override',
        'user_id' => $admin->id,
    ]);
});

it('search-users returns matching users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Alice Smith', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@example.com']);

    $response = $this->actingAs($admin)->get('/admin/feature-flags/search-users?q=alice');

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toHaveCount(1);
    expect($data[0]['name'])->toBe('Alice Smith');
});

it('search-users rejects query shorter than 2 characters', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/feature-flags/search-users?q=a');

    $response->assertStatus(422);
    $response->assertJson(['success' => false]);
});

it('getUserOverrides returns targeted users for flag', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => $user->id, 'enabled' => true]);

    $response = $this->actingAs($admin)->get('/admin/feature-flags/billing/users');

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toHaveCount(1);
    expect($data[0])->toMatchArray([
        'user_id' => $user->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'enabled' => true,
    ]);
});

it('stores reason when setting global override', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/billing', [
        'enabled' => true,
        'reason' => 'Beta rollout',
    ]);

    $response->assertRedirect();

    $override = FeatureFlagOverride::where('flag', 'billing')->whereNull('user_id')->first();
    expect($override->reason)->toBe('Beta rollout');
    expect($override->changed_by)->toBe($admin->id);
});

it('stores reason when setting user override', function () {
    $admin = User::factory()->superAdmin()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/feature-flags/billing/users', [
        'user_id' => $targetUser->id,
        'enabled' => true,
        'reason' => 'Early access',
    ]);

    $response->assertRedirect();

    $override = FeatureFlagOverride::where('flag', 'billing')
        ->where('user_id', $targetUser->id)
        ->first();

    expect($override->reason)->toBe('Early access');
    expect($override->changed_by)->toBe($admin->id);
});

it('accepts null reason when setting override', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/billing', [
        'enabled' => true,
    ]);

    $response->assertRedirect();

    $override = FeatureFlagOverride::where('flag', 'billing')->whereNull('user_id')->first();
    expect($override->reason)->toBeNull();
});

// removeGlobal

it('audit logs global override removal', function () {
    $admin = User::factory()->superAdmin()->create();
    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => null, 'enabled' => true]);

    $response = $this->actingAs($admin)->delete('/admin/feature-flags/billing');

    $response->assertRedirect();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.feature_flag.global_override_removed',
        'user_id' => $admin->id,
    ]);
});

it('returns error when removing global override for unknown flag', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->delete('/admin/feature-flags/unknown_flag');

    $response->assertRedirect();
    $response->assertSessionHasErrors('flag');
});

// removeUserOverride

it('audit logs user override removal', function () {
    $admin = User::factory()->superAdmin()->create();
    $targetUser = User::factory()->create();
    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => $targetUser->id, 'enabled' => true]);

    $response = $this->actingAs($admin)->delete("/admin/feature-flags/billing/users/{$targetUser->id}");

    $response->assertRedirect();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.feature_flag.user_override_removed',
        'user_id' => $admin->id,
    ]);
});

it('returns error when removing user override for unknown flag', function () {
    $admin = User::factory()->superAdmin()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/feature-flags/unknown_flag/users/{$targetUser->id}");

    $response->assertRedirect();
    $response->assertSessionHasErrors('user_override');
});

// removeAllUserOverrides

it('audit logs bulk user override removal', function () {
    $admin = User::factory()->superAdmin()->create();
    $user1 = User::factory()->create();
    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => $user1->id, 'enabled' => true]);

    $response = $this->actingAs($admin)->delete('/admin/feature-flags/billing/users');

    $response->assertRedirect();

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.feature_flag.all_user_overrides_removed',
        'user_id' => $admin->id,
    ]);
});

it('removes all user overrides is idempotent when no overrides exist', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->delete('/admin/feature-flags/billing/users');

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

it('returns error when removing all user overrides for unknown flag', function () {
    $admin = User::factory()->superAdmin()->create();

    $response = $this->actingAs($admin)->delete('/admin/feature-flags/unknown_flag/users');

    $response->assertRedirect();
    $response->assertSessionHasErrors('user_overrides');
});

// getTargetedUsers auth protection

it('getTargetedUsers redirects guests to login', function () {
    $response = $this->get('/admin/feature-flags/billing/users');

    $response->assertRedirect('/login');
});

it('getTargetedUsers returns 403 for non-admin users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get('/admin/feature-flags/billing/users');

    $response->assertStatus(403);
});

it('getTargetedUsers returns 422 for unknown flag', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/feature-flags/unknown_flag/users');

    $response->assertStatus(422);
    $response->assertJson(['success' => false]);
});

it('getTargetedUsers returns empty array when no user overrides exist', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/feature-flags/billing/users');

    $response->assertStatus(200);
    expect($response->json())->toBe([]);
});

// searchUsers edge cases

it('search-users rejects query longer than 100 characters', function () {
    $admin = User::factory()->admin()->create();
    $longQuery = str_repeat('a', 101);

    $response = $this->actingAs($admin)->get("/admin/feature-flags/search-users?q={$longQuery}");

    $response->assertStatus(422);
    $response->assertJson(['success' => false]);
});

it('search-users returns empty array when no users match', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/feature-flags/search-users?q=zzznomatch');

    $response->assertStatus(200);
    expect($response->json())->toBe([]);
});

// super_admin enforcement: regular admins cannot perform mutations

it('regular admin cannot update global feature flag override', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);

    $this->actingAs($admin)
        ->patch('/admin/feature-flags/billing', ['enabled' => true])
        ->assertForbidden();
});

it('regular admin cannot remove global feature flag override', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);

    $this->actingAs($admin)
        ->delete('/admin/feature-flags/billing')
        ->assertForbidden();
});

it('regular admin cannot add user feature flag override', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $targetUser = User::factory()->create();

    $this->actingAs($admin)
        ->post('/admin/feature-flags/billing/users', [
            'user_id' => $targetUser->id,
            'enabled' => true,
        ])
        ->assertForbidden();
});

it('regular admin cannot remove user feature flag override', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);
    $targetUser = User::factory()->create();

    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => $targetUser->id, 'enabled' => true]);

    $this->actingAs($admin)
        ->delete("/admin/feature-flags/billing/users/{$targetUser->id}")
        ->assertForbidden();
});

it('regular admin cannot remove all user feature flag overrides', function () {
    $admin = User::factory()->admin()->create(['super_admin' => false]);

    $this->actingAs($admin)
        ->delete('/admin/feature-flags/billing/users')
        ->assertForbidden();
});

it('rejects flag parameter with path traversal characters on patch', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch('/admin/feature-flags/../../etc/passwd', ['enabled' => true])
        ->assertStatus(404);
});

it('rejects flag parameter with uppercase letters on patch', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->patch('/admin/feature-flags/BILLING', ['enabled' => true])
        ->assertStatus(404);
});

it('rejects flag parameter with hyphens on delete global', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->delete('/admin/feature-flags/some-flag')
        ->assertStatus(404);
});

it('rejects flag parameter with path traversal on get users', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/feature-flags/../users/list')
        ->assertStatus(404);
});

it('rejects flag parameter with special chars on add user override', function () {
    $admin = User::factory()->superAdmin()->create();
    $targetUser = User::factory()->create();

    // Use '@' instead of '%00' (null byte): null bytes are rejected at the HTTP/PHP layer
    // before reaching the router, so %00 does not exercise the route constraint itself.
    // '@' is a non-null-byte special character guaranteed to reach the router's [a-z_]+ check.
    $this->actingAs($admin)
        ->post('/admin/feature-flags/billing@null/users', [
            'user_id' => $targetUser->id,
            'enabled' => true,
        ])
        ->assertStatus(404);
});

it('accepts valid lowercase underscore flag names', function () {
    $admin = User::factory()->superAdmin()->create();

    // Use a real registered flag name to decouple route-constraint check from
    // controller behavior. The constraint [a-z_]+ should pass this to the controller
    // which always redirects (never 404) regardless of whether the override succeeds.
    $response = $this->actingAs($admin)
        ->patch('/admin/feature-flags/billing', ['enabled' => true]);

    $response->assertRedirect(); // reaches controller — not 404 from route constraint
});

it('route constraint accepts all registered flag names', function () {
    // Every key in config/features.php must match the route constraint [a-z_]+.
    // If a flag key contains hyphens or uppercase letters it cannot be managed
    // via the admin UI (all matching routes would silently 404).
    $flags = array_keys(config('features', []));

    expect($flags)->not->toBeEmpty('No feature flags found in config/features.php');

    foreach ($flags as $flag) {
        expect(preg_match('/^[a-z_]+$/', $flag))->toBe(
            1,
            "Flag '{$flag}' contains characters outside [a-z_] and cannot be managed via admin feature-flag routes"
        );
    }
});
