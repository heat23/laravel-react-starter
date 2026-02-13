<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

beforeEach(function () {
    registerAdminRoutes();
});

/*
|--------------------------------------------------------------------------
| Pagination Edge Cases
|--------------------------------------------------------------------------
*/

it('returns empty page when page exceeds last page', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/users?page=999');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('users.data', 0)
    );
});

it('handles page=0 gracefully', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/users?page=0');

    // Laravel treats page=0 the same as page=1
    $response->assertStatus(200);
});

it('handles negative page gracefully', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/users?page=-1');

    $response->assertStatus(200);
});

it('handles non-numeric page gracefully', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/users?page=abc');

    $response->assertStatus(200);
});

/*
|--------------------------------------------------------------------------
| Search SQL Injection Prevention
|--------------------------------------------------------------------------
*/

it('handles search with SQL wildcard characters safely', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Normal User']);

    $response = $this->actingAs($admin)->get('/admin/users?search='.urlencode('%'));

    $response->assertStatus(200);
});

it('handles search with single quote safely', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get("/admin/users?search=O'Brien");

    $response->assertStatus(200);
});

it('handles search with underscore wildcard safely', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['name' => 'Test_User']);

    $response = $this->actingAs($admin)->get('/admin/users?search='.urlencode('_'));

    $response->assertStatus(200);
});

it('handles search with backslash safely', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/users?search='.urlencode('test\\'));

    $response->assertStatus(200);
});

/*
|--------------------------------------------------------------------------
| Impersonation Security
|--------------------------------------------------------------------------
*/

it('impersonated user cannot access admin routes', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    // Start impersonation (switches auth to $user)
    $this->actingAs($admin)->post("/admin/users/{$user->id}/impersonate");
    $this->assertAuthenticatedAs($user);

    // Impersonated user (non-admin) should be blocked from admin routes
    $response = $this->actingAs($user)->get('/admin');
    $response->assertStatus(403);
});

it('handles tampered impersonation session gracefully', function () {
    $user = User::factory()->create();

    // Put garbage in the encrypted session field
    $response = $this->actingAs($user)
        ->withSession(['admin_impersonating_from' => 'tampered-not-encrypted-value'])
        ->post('/admin/impersonate/stop');

    // Should safely redirect to login (decryption failure)
    $response->assertRedirect(route('login'));
});

it('handles impersonation with non-existent admin ID', function () {
    $user = User::factory()->create();

    // Encrypt a non-existent admin ID
    $response = $this->actingAs($user)
        ->withSession([
            'admin_impersonating_from' => Crypt::encryptString('99999'),
            'admin_impersonating_name' => 'Ghost Admin',
        ])
        ->post('/admin/impersonate/stop');

    // Admin not found — should redirect to login
    $response->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| Cache Invalidation
|--------------------------------------------------------------------------
*/

it('dashboard stats reflect changes after cache expires', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    // First request caches stats
    $response1 = $this->actingAs($admin)->get('/admin');
    $response1->assertInertia(fn ($page) => $page
        ->where('stats.total_users', 1)
    );

    // Create more users
    User::factory()->count(5)->create();

    // Stats should still be cached (same value)
    $response2 = $this->actingAs($admin)->get('/admin');
    $response2->assertInertia(fn ($page) => $page
        ->where('stats.total_users', 1) // cached
    );

    // Flush cache — stats should update
    Cache::flush();

    $response3 = $this->actingAs($admin)->get('/admin');
    $response3->assertInertia(fn ($page) => $page
        ->where('stats.total_users', 6)
    );
});

/*
|--------------------------------------------------------------------------
| Export Edge Cases
|--------------------------------------------------------------------------
*/

it('exports empty CSV when no audit logs match filters', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->get('/admin/audit-logs/export?event=nonexistent.event');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('export respects date range filters', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create([
        'event' => 'test.event',
        'user_id' => $admin->id,
        'ip' => '127.0.0.1',
        'metadata' => [],
    ]);

    // Use a future date range that shouldn't match
    $response = $this->actingAs($admin)
        ->get('/admin/audit-logs/export?from=2099-01-01&to=2099-12-31');

    $response->assertStatus(200);
});

/*
|--------------------------------------------------------------------------
| Concurrent Admin Actions
|--------------------------------------------------------------------------
*/

it('two admins toggling same user does not cause errors', function () {
    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();
    $user = User::factory()->create();

    // Both admins toggle admin on the same user
    $response1 = $this->actingAs($admin1)->patch("/admin/users/{$user->id}/toggle-admin");
    $response1->assertRedirect();

    $response2 = $this->actingAs($admin2)->patch("/admin/users/{$user->id}/toggle-admin");
    $response2->assertRedirect();

    // User should be toggled twice (back to original)
    $user->refresh();
    expect($user->is_admin)->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Config Page Security
|--------------------------------------------------------------------------
*/

it('config page does not expose sensitive driver info', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/config');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('feature_flags')
        ->has('warnings')
        ->has('environment')
        // Should NOT have sensitive fields
        ->missing('environment.cache_driver')
        ->missing('environment.queue_driver')
        ->missing('environment.session_driver')
        ->missing('environment.database_driver')
        ->missing('environment.app_debug')
        ->missing('environment.app_url')
    );
});
