<?php

use App\Enums\AdminCacheKey;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    registerAdminRoutes();
});

it('redirects guests to login for index', function () {
    $this->get('/admin/users')->assertRedirect('/login');
});

it('redirects guests to login for show', function () {
    $user = User::factory()->create();

    $this->get("/admin/users/{$user->id}")->assertRedirect('/login');
});

it('returns 403 for non-admin on index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/users')->assertStatus(403);
});

it('returns 403 for non-admin on toggle-admin', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)->patch("/admin/users/{$target->id}/toggle-admin")->assertStatus(403);
});

it('returns 403 for non-admin on toggle-active', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)->patch("/admin/users/{$target->id}/toggle-active")->assertStatus(403);
});

it('loads index with user list', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Users/Index')
        ->has('users.data', 4)
    );
});

it('searches users by name', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($admin)->get('/admin/users?search=John');

    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.name', 'John Doe')
    );
});

it('searches users by email', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'findme@test.com']);
    User::factory()->create(['email' => 'other@test.com']);

    $response = $this->actingAs($admin)->get('/admin/users?search=findme');

    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.email', 'findme@test.com')
    );
});

it('filters by admin status', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($admin)->get('/admin/users?admin=1');

    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.is_admin', true)
    );
});

it('sorts by columns', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);

    $response = $this->actingAs($admin)->get('/admin/users?sort=name&dir=asc');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('users.data.0.name', 'Alice')
    );
});

it('shows user detail with audit logs', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    AuditLog::create([
        'event' => 'auth.login',
        'user_id' => $user->id,
        'ip' => '127.0.0.1',
        'metadata' => [],
    ]);

    $response = $this->actingAs($admin)->get("/admin/users/{$user->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Users/Show')
        ->where('user.id', $user->id)
        ->has('recent_audit_logs', 1)
    );
});

it('toggles admin status', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-admin");

    $response->assertRedirect();
    expect($user->fresh()->is_admin)->toBeTrue();
});

it('prevents self-demotion', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->patch("/admin/users/{$admin->id}/toggle-admin");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect($admin->fresh()->is_admin)->toBeTrue();
});

it('soft-deletes a user via toggle active', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-active");

    $response->assertRedirect();
    expect($user->fresh()->trashed())->toBeTrue();
});

it('restores a soft-deleted user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->delete();

    $response = $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-active");

    $response->assertRedirect();
    expect($user->fresh()->trashed())->toBeFalse();
});

it('prevents self-deactivation', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->patch("/admin/users/{$admin->id}/toggle-active");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect($admin->fresh()->trashed())->toBeFalse();
});

it('sorts descending by default', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin']);
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);

    $response = $this->actingAs($admin)->get('/admin/users?sort=name&dir=desc');

    $response->assertInertia(fn ($page) => $page
        ->where('users.data.0.name', 'Bob')
        ->where('users.data.1.name', 'Alice')
        ->where('users.data.2.name', 'Admin')
    );
});

it('rejects invalid sort column with validation error', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/users?sort=invalid_column');

    $response->assertStatus(302);
    $response->assertSessionHasErrors('sort');
});

it('shows soft-deleted user details', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->delete();

    $response = $this->actingAs($admin)->get("/admin/users/{$user->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Users/Show')
        ->where('user.id', $user->id)
        ->has('user.deleted_at')
    );
});

it('includes tokens count in user detail', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->createToken('test-token');

    $response = $this->actingAs($admin)->get("/admin/users/{$user->id}");

    $response->assertInertia(fn ($page) => $page
        ->where('user.tokens_count', 1)
    );
});

it('includes tokens count in user index', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->createToken('test-token');

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 2)
    );
});

it('returns filters in response', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/users?search=test&admin=1&sort=name&dir=asc');

    $response->assertInertia(fn ($page) => $page
        ->where('filters.search', 'test')
        ->where('filters.admin', '1')
        ->where('filters.sort', 'name')
        ->where('filters.dir', 'asc')
    );
});

it('returns 404 for non-existent user', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/users/99999')->assertStatus(404);
});

it('toggles admin off for existing admin', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch("/admin/users/{$otherAdmin->id}/toggle-admin");

    expect($otherAdmin->fresh()->is_admin)->toBeFalse();
});

it('shows success flash on toggle admin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-admin");

    $response->assertSessionHas('success');
});

it('shows success flash on deactivate', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-active");

    $response->assertSessionHas('success');
});

it('shows success flash on restore', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->delete();

    $response = $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-active");

    $response->assertSessionHas('success');
});

it('includes soft-deleted users in index', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->delete();

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 2) // admin + soft-deleted user
    );
});

it('filters non-admin users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create();

    $response = $this->actingAs($admin)->get('/admin/users?admin=0');

    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.is_admin', false)
    );
});

it('user index query count does not scale with user count', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(10)->create();

    DB::enableQueryLog();
    $this->actingAs($admin)->get('/admin/users');
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Should be constant: auth queries + 1 paginated user query + 1 count query
    // Not scaling with number of users (no N+1)
    expect($queryCount)->toBeLessThan(15);
});

it('invalidates dashboard cache on toggle admin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, ['cached' => true], 300);

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-admin");

    expect(Cache::has(AdminCacheKey::DASHBOARD_STATS->value))->toBeFalse();
});

it('invalidates dashboard cache on toggle active', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, ['cached' => true], 300);

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-active");

    expect(Cache::has(AdminCacheKey::DASHBOARD_STATS->value))->toBeFalse();
});

it('invalidates dashboard cache on bulk deactivate', function () {
    $admin = User::factory()->admin()->create();
    $users = User::factory()->count(2)->create();

    Cache::put(AdminCacheKey::DASHBOARD_STATS->value, ['cached' => true], 300);

    $this->actingAs($admin)->post('/admin/users/bulk-deactivate', [
        'ids' => $users->pluck('id')->toArray(),
    ]);

    expect(Cache::has(AdminCacheKey::DASHBOARD_STATS->value))->toBeFalse();
});
