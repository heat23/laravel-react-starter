<?php

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    registerAdminRoutes();
    // Flush all cache (including rate-limiter state) so each test starts clean.
    Cache::flush();
});

afterEach(function () {
    Cache::flush();
});

// ── View routes throttled at 30/min (per-route via named limiter) ───────────

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

    for ($i = 0; $i < 30; $i++) {
        $this->actingAs($admin)->get('/admin/users')->assertStatus(200);
    }

    // 31st request must be rate-limited
    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertStatus(429);
});

// ── Per-route isolation: named limiters give each route its own bucket ──────

it('admin-read limiter isolates buckets per route — exhausting one route does not block another', function () {
    $admin = User::factory()->admin()->create();

    // Exhaust the 30/min budget on /admin/schedule
    for ($i = 0; $i < 30; $i++) {
        $this->actingAs($admin)->get('/admin/schedule')->assertStatus(200);
    }

    // /admin/schedule is now rate-limited
    $this->actingAs($admin)->get('/admin/schedule')->assertStatus(429);

    // /admin/sessions has its own bucket — must still be accessible
    $this->actingAs($admin)->get('/admin/sessions')->assertStatus(200);

    // /admin/cache also has its own bucket — must still be accessible
    $this->actingAs($admin)->get('/admin/cache')->assertStatus(200);
});

// ── Sensitive operations throttled at 5/min ─────────────────────────────────

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

    $response->assertHeader('X-RateLimit-Remaining', '0');

    // 6th request must be rate-limited
    $this->actingAs($admin)
        ->post("/admin/users/{$target->id}/impersonate")
        ->assertStatus(429);
});

// ── send-password-reset throttled at 5/min via admin-sensitive ─────────────

it('send-password-reset endpoint enforces 5/min admin-sensitive limit', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->actingAs($admin)
            ->post("/admin/users/{$target->id}/send-password-reset");
    }

    // 6th request must be rate-limited
    $this->actingAs($admin)
        ->post("/admin/users/{$target->id}/send-password-reset")
        ->assertStatus(429);
});

// ── Stop-impersonation throttled at 10/min ──────────────────────────────────

it('stop-impersonation route redirects for authenticated users without an active session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/impersonate/stop')
        ->assertStatus(302);
});

// ── Export routes throttled at 10/min ───────────────────────────────────────

it('users export does not throttle on first request', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/users/export')
        ->assertSuccessful();
});

// ── View routes covering previously-unthrottled endpoints ───────────────────

it('admin schedule is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/schedule')
        ->assertStatus(200);
});

it('admin feedback index is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/feedback')
        ->assertStatus(200);
});

it('admin sessions index is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/sessions')
        ->assertStatus(200);
});

it('admin cache index is accessible with 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/cache')
        ->assertStatus(200);
});

it('admin schedule enforces 30/min view throttle', function () {
    $admin = User::factory()->admin()->create();

    for ($i = 0; $i < 30; $i++) {
        $this->actingAs($admin)->get('/admin/schedule')->assertStatus(200);
    }

    // 31st request must be rate-limited
    $this->actingAs($admin)
        ->get('/admin/schedule')
        ->assertStatus(429);
});

// ── admin-write exhaustion on reclassified routes ─────────────────────────

it('PATCH /admin/feedback enforces admin-write 10/min on reclassified route', function () {
    $admin = User::factory()->admin()->create();
    $feedback = Feedback::factory()->create();

    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($admin)
            ->patch("/admin/feedback/{$feedback->id}", []);
    }

    // 11th request must be rate-limited at admin-write limit
    $this->actingAs($admin)
        ->patch("/admin/feedback/{$feedback->id}", [])
        ->assertStatus(429);
});

// ── Named limiter smoke test: registrations resolve correctly ─────────────

it('admin rate limiters are registered and return Limit instances', function () {
    $admin = User::factory()->admin()->create();

    $request = Request::create('/admin', 'GET');
    $request->setUserResolver(fn () => $admin);
    $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

    $readLimiter = RateLimiter::limiter('admin-read');
    $writeLimiter = RateLimiter::limiter('admin-write');
    $sensitiveLimiter = RateLimiter::limiter('admin-sensitive');

    expect($readLimiter)->toBeCallable();
    expect($writeLimiter)->toBeCallable();
    expect($sensitiveLimiter)->toBeCallable();

    $readResult = $readLimiter($request);
    $writeResult = $writeLimiter($request);
    $sensitiveResult = $sensitiveLimiter($request);

    expect($readResult)->toBeInstanceOf(Limit::class);
    expect($writeResult)->toBeInstanceOf(Limit::class);
    expect($sensitiveResult)->toBeInstanceOf(Limit::class);
});

// ── Global group-level fallback at 60/min ───────────────────────────────────

it('global admin group enforces 60/min fallback throttle across routes', function () {
    $admin = User::factory()->admin()->create();

    // The group-level throttle:60,1,admin: uses a prefixed key separate from
    // the named per-route limiters (which key by admin-read:<route>:<user>).
    // We exhaust the group budget by hitting three different GET routes
    // (20 requests each = 60 total group hits). Each route's named
    // admin-read limiter has its own bucket (30/min), so the per-route
    // limiters won't fire — only the group limiter will.
    $routes = ['/admin/schedule', '/admin/sessions', '/admin/cache'];

    $requestCount = 0;
    foreach ($routes as $route) {
        for ($i = 0; $i < 20; $i++) {
            $response = $this->actingAs($admin)->get($route);
            $response->assertStatus(200, "Request #{$requestCount} to {$route} should be 200 (group budget not yet exhausted)");
            $requestCount++;
        }
    }

    // 61st group-throttle hit must be blocked by throttle:60,1,admin:
    // even though each individual route still has per-route budget remaining.
    $this->actingAs($admin)
        ->get('/admin/feedback')
        ->assertStatus(429);
});
