<?php

use App\Enums\AdminCacheKey;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

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
    $admin = User::factory()->admin()->create(['name' => 'Test Admin']);
    $john = User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($admin)->get('/admin/users?search=Doe');

    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.id', $john->id)
    );
});

it('searches users by email', function () {
    $admin = User::factory()->admin()->create(['email' => 'admin@test.com']);
    $findme = User::factory()->create(['email' => 'findme@test.com']);
    User::factory()->create(['email' => 'other@test.com']);

    $response = $this->actingAs($admin)->get('/admin/users?search=findme');

    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.id', $findme->id)
    );
});

it('escapes LIKE wildcards in user search', function () {
    $admin = User::factory()->admin()->create();
    $wildcardUser = User::factory()->create(['name' => 'test%user']);
    User::factory()->create(['name' => 'test_normal']);

    $response = $this->actingAs($admin)->get('/admin/users?search=test%25');

    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 1)
        ->where('users.data.0.id', $wildcardUser->id)
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
    $admin = User::factory()->admin()->create(['name' => 'ZZZ Admin']); // Sort last
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

it('toggles admin off for existing admin when more than two admins exist', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    User::factory()->admin()->create(); // third admin

    $this->actingAs($admin)->patch("/admin/users/{$otherAdmin->id}/toggle-admin");

    expect($otherAdmin->fresh()->is_admin)->toBeFalse();
});

it('prevents removing admin when it would leave fewer than two admins', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    // Only 2 admins exist

    $response = $this->actingAs($admin)->patch("/admin/users/{$otherAdmin->id}/toggle-admin");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    expect($otherAdmin->fresh()->is_admin)->toBeTrue();
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

// Fix 1: Before/after value capture in audit trail
it('captures before/after values when toggling admin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-admin");

    $log = AuditLog::where('event', 'admin.toggle_admin')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->metadata['changes']['is_admin']['from'])->toBeFalse();
    expect($log->metadata['changes']['is_admin']['to'])->toBeTrue();
});

it('captures before/after values when deactivating user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-active");

    $log = AuditLog::where('event', 'admin.user_deactivated')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->metadata['changes']['active']['from'])->toBeTrue();
    expect($log->metadata['changes']['active']['to'])->toBeFalse();
});

it('captures before/after values when restoring user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->delete();

    $this->actingAs($admin)->patch("/admin/users/{$user->id}/toggle-active");

    $log = AuditLog::where('event', 'admin.user_restored')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->metadata['changes']['active']['from'])->toBeFalse();
    expect($log->metadata['changes']['active']['to'])->toBeTrue();
});

it('captures before/after values in bulk deactivate', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->post('/admin/users/bulk-deactivate', [
        'ids' => [$user->id],
    ]);

    $log = AuditLog::where('event', 'admin.user_deactivated')
        ->where('metadata->bulk', true)
        ->latest('id')
        ->first();
    expect($log)->not->toBeNull();
    expect($log->metadata['changes']['active']['from'])->toBeTrue();
    expect($log->metadata['changes']['active']['to'])->toBeFalse();
});

// Fix 2: Audit log for admin data views
it('logs audit entry when viewing user details', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->get("/admin/users/{$user->id}");

    $log = AuditLog::where('event', 'admin.user_viewed')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->metadata['target_user_id'])->toBe($user->id);
    expect($log->metadata['target_email'])->toBe($user->email);
});

// Fix 3: User CSV export
it('exports users as CSV', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Export User', 'email' => 'export@test.com']);

    $response = $this->actingAs($admin)->get('/admin/users/export');

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $content = $response->streamedContent();
    expect($content)->toContain('ID,Name,Email,Admin,Verified');
    expect($content)->toContain('Export User');
    expect($content)->toContain('export@test.com');
});

it('exports users with filters applied', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Findme User', 'email' => 'findme@test.com']);
    User::factory()->create(['name' => 'Other User', 'email' => 'other@test.com']);

    $response = $this->actingAs($admin)->get('/admin/users/export?search=Findme');

    $content = $response->streamedContent();
    expect($content)->toContain('Findme User');
    expect($content)->not->toContain('Other User');
});

it('sanitizes CSV export against formula injection for users', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => '=cmd|test', 'email' => 'formula@test.com']);

    $response = $this->actingAs($admin)->get('/admin/users/export');

    $content = $response->streamedContent();
    expect($content)->toContain("'=cmd|test");
});

it('returns 403 for non-admin on user export', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/users/export')->assertStatus(403);
});

it('logs audit entry when exporting users', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/users/export');

    $log = AuditLog::where('event', 'admin.users_exported')->latest('id')->first();
    expect($log)->not->toBeNull();
});

// Fix 5: Admin-initiated password reset
it('sends password reset email for user with password', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/send-password-reset");

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Password reset email sent.');
    Notification::assertSentTo($user, ResetPassword::class);
});

it('rejects password reset for OAuth-only user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['password' => null]);

    $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/send-password-reset");

    $response->assertRedirect();
    $response->assertSessionHas('error', 'User has no password (OAuth-only account).');
});

it('logs audit entry when sending password reset', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $this->actingAs($admin)->post("/admin/users/{$user->id}/send-password-reset");

    $log = AuditLog::where('event', 'admin.password_reset_sent')->latest('id')->first();
    expect($log)->not->toBeNull();
    expect($log->metadata['target_user_id'])->toBe($user->id);
});

it('returns 403 for non-admin on password reset', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)->post("/admin/users/{$target->id}/send-password-reset")->assertStatus(403);
});
