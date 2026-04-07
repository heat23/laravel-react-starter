<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    registerAdminRoutes();
    // Flush all cache (including rate-limiter state) so each test starts clean.
    // RateLimiter::clear('') only clears the empty-key bucket, not the per-route/per-user
    // keys that throttle middleware generates, which caused state bleed between tests.
    Cache::flush();
});

afterEach(function () {
    Cache::flush();
});

// ── View routes throttled at 30/min ──────────────────────────────────────────

it('admin dashboard is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertStatus(200);
});

it('admin analytics is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/analytics')
        ->assertStatus(200);
});

it('admin users index is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertStatus(200);
});

it('admin users create is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/users/create')
        ->assertStatus(200);
});

it('admin health is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/health')
        ->assertStatus(200);
});

it('admin config is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/config')
        ->assertStatus(200);
});

it('admin users index enforces 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    // Exhaust the 30/min per-route limit — all should succeed.
    // The group throttle:60 uses an 'admin:' prefix so its key is separate from
    // the route-level throttle:30 key; each middleware enforces its own counter.
    for ($i = 0; $i < 30; $i++) {
        $this->actingAs($admin)->get('/admin/users')->assertStatus(200);
    }

    // 31st request must be rate-limited
    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertStatus(429);
});

// ── Sensitive operations throttled at 5/min ──────────────────────────────────

it('impersonation endpoint enforces super_admin gate before throttle fires', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create();

    // A regular admin (not super_admin) gets 403 — auth guard fires before throttle
    $this->actingAs($admin)
        ->post("/admin/users/{$target->id}/impersonate")
        ->assertStatus(403);
});

it('impersonation endpoint returns 429 after exhausting 5/min limit', function () {
    $admin = User::factory()->superAdmin()->create();

    // Use a soft-deleted target; throttle middleware runs before the controller
    // so business-logic short-circuits still count against the rate limit.
    $target = User::factory()->create();
    $target->delete();

    $response = null;
    for ($i = 0; $i < 5; $i++) {
        $response = $this->actingAs($admin)
            ->post("/admin/users/{$target->id}/impersonate");
    }

    // After 5 requests the route-level throttle:5 counter is exhausted.
    // X-RateLimit-Remaining comes from the innermost throttle middleware that
    // fired; the group throttle uses a separate 'admin:' key so it doesn't
    // interfere. On the 5th allowed request remaining hits zero.
    $response->assertHeader('X-RateLimit-Remaining', '0');

    // 6th request must be rate-limited
    $this->actingAs($admin)
        ->post("/admin/users/{$target->id}/impersonate")
        ->assertStatus(429);
});

// ── Stop-impersonation throttled at 10/min ───────────────────────────────────

it('stop-impersonation route redirects for authenticated users without an active session', function () {
    $user = User::factory()->create();

    // Without an active impersonation session the controller redirects to dashboard (302)
    $this->actingAs($user)
        ->post('/admin/impersonate/stop')
        ->assertStatus(302);
});

// ── Export routes throttled at 10/min ────────────────────────────────────────

it('users export does not throttle on first request', function () {
    $admin = User::factory()->admin()->create();

    // Export returns a streamed CSV response — assert it is not rate-limited
    $this->actingAs($admin)
        ->get('/admin/users/export')
        ->assertSuccessful();
});

// ── Global fallback at 60/min ────────────────────────────────────────────────

it('global admin group applies 60/min fallback for routes without specific throttle', function () {
    $admin = User::factory()->admin()->create();

    // /admin/schedule has no per-route throttle — falls back to global 60/min
    $this->actingAs($admin)
        ->get('/admin/schedule')
        ->assertStatus(200);
});

it('global admin group enforces 60/min fallback throttle', function () {
    $admin = User::factory()->admin()->create();

    // /admin/schedule has no per-route override — only the group-level throttle:60 applies.
    // The group throttle uses an 'admin:' key prefix, so there is no collision with
    // route-level throttles and the full 60-request budget is available.
    for ($i = 0; $i < 60; $i++) {
        $this->actingAs($admin)->get('/admin/schedule')->assertStatus(200);
    }

    // 61st request must be rate-limited
    $this->actingAs($admin)
        ->get('/admin/schedule')
        ->assertStatus(429);
});
