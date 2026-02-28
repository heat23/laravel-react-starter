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
    $admin = User::factory()->admin()->create();

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
    $admin = User::factory()->admin()->create();

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
    $admin = User::factory()->admin()->create();

    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => null, 'enabled' => true]);

    $response = $this->actingAs($admin)->delete('/admin/feature-flags/billing');

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(FeatureFlagOverride::where('flag', 'billing')->whereNull('user_id')->first())
        ->toBeNull();
});

it('prevents any override on protected admin flag', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/admin', [
        'enabled' => false,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('flag');
});

it('prevents per-user override on protected admin flag', function () {
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($admin)->post('/admin/feature-flags/admin/users', [
        'user_id' => $targetUser->id,
        'enabled' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('user_override');
});

it('adds a user override', function () {
    $admin = User::factory()->admin()->create();
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
    $admin = User::factory()->admin()->create();
    $targetUser = User::factory()->create();

    FeatureFlagOverride::create(['flag' => 'billing', 'user_id' => $targetUser->id, 'enabled' => true]);

    $response = $this->actingAs($admin)->delete("/admin/feature-flags/billing/users/{$targetUser->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(FeatureFlagOverride::where('flag', 'billing')->where('user_id', $targetUser->id)->first())
        ->toBeNull();
});

it('removes all user overrides for a flag', function () {
    $admin = User::factory()->admin()->create();
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
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/unknown_flag', [
        'enabled' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('flag');
});

it('returns validation error for non-existent user_id', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/feature-flags/billing/users', [
        'user_id' => 99999,
        'enabled' => true,
    ]);

    // Inertia redirects with validation errors in session
    $response->assertSessionHasErrors('user_id');
});

it('audit logs global override changes', function () {
    $admin = User::factory()->admin()->create();

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
    $admin = User::factory()->admin()->create();
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
    $admin = User::factory()->admin()->create();

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
    $admin = User::factory()->admin()->create();
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
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->patch('/admin/feature-flags/billing', [
        'enabled' => true,
    ]);

    $response->assertRedirect();

    $override = FeatureFlagOverride::where('flag', 'billing')->whereNull('user_id')->first();
    expect($override->reason)->toBeNull();
});
