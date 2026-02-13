<?php

use App\Models\AuditLog;
use App\Models\User;

beforeEach(function () {
    registerAdminRoutes();
});

it('returns 403 for non-admin on impersonate', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)->post("/admin/users/{$target->id}/impersonate")->assertStatus(403);
});

it('allows admin to impersonate a regular user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/impersonate");

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('cannot impersonate self', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post("/admin/users/{$admin->id}/impersonate");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertAuthenticatedAs($admin);
});

it('cannot impersonate another admin', function () {
    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();

    $response = $this->actingAs($admin1)->post("/admin/users/{$admin2->id}/impersonate");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertAuthenticatedAs($admin1);
});

it('cannot impersonate soft-deleted user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $user->delete();

    $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/impersonate");

    $response->assertRedirect();
    $response->assertSessionHas('error', 'Cannot impersonate a deactivated user.');
    $this->assertAuthenticatedAs($admin);
});

it('allows stopping impersonation as unverified user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['email_verified_at' => null]);

    $response = $this->actingAs($user)
        ->withSession(impersonationSession($admin->id, $admin->name))
        ->post('/admin/impersonate/stop');

    $response->assertRedirect(route('admin.users.index'));
    $this->assertAuthenticatedAs($admin);
});

it('stops impersonation and returns to admin user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    // Start impersonation
    $this->actingAs($admin)->post("/admin/users/{$user->id}/impersonate");
    $this->assertAuthenticatedAs($user);

    // Stop impersonation
    $response = $this->actingAs($user)
        ->withSession(impersonationSession($admin->id, $admin->name))
        ->post('/admin/impersonate/stop');

    $response->assertRedirect(route('admin.users.index'));
    $this->assertAuthenticatedAs($admin);
});

it('creates audit log on impersonation start', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->post("/admin/users/{$user->id}/impersonate");

    // AuditLog is dispatched via a job, so process the queue
    $this->artisan('queue:work', ['--once' => true, '--queue' => 'default']);

    $log = AuditLog::where('event', 'admin.impersonation_started')->first();
    expect($log)->not->toBeNull();
    expect($log->metadata['target_user_id'])->toBe($user->id);
});

it('creates audit log on impersonation stop', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(impersonationSession($admin->id, $admin->name))
        ->post('/admin/impersonate/stop');

    $this->artisan('queue:work', ['--once' => true, '--queue' => 'default']);

    $log = AuditLog::where('event', 'admin.impersonation_stopped')->first();
    expect($log)->not->toBeNull();
    expect($log->metadata['admin_id'])->toBe($admin->id);
});

it('logs out when admin was soft-deleted during impersonation', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    // Soft-delete the admin while user is being impersonated
    $admin->delete();

    $response = $this->actingAs($user)
        ->withSession(impersonationSession($admin->id, $admin->name))
        ->post('/admin/impersonate/stop');

    $response->assertRedirect(route('login'));
});

it('shares impersonation data as Inertia prop', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    // Simulate impersonation session
    $response = $this->actingAs($user)
        ->withSession(impersonationSession($admin->id, $admin->name))
        ->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('auth.impersonating')
        ->where('auth.impersonating.admin_name', $admin->name)
    );
});

it('stop without session redirects to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/impersonate/stop');

    $response->assertRedirect(route('dashboard'));
});

it('logs out when admin lost admin status during impersonation', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    // Remove admin status while impersonating
    $admin->is_admin = false;
    $admin->save();

    $response = $this->actingAs($user)
        ->withSession(impersonationSession($admin->id, $admin->name))
        ->post('/admin/impersonate/stop');

    $response->assertRedirect(route('login'));
});

it('returns 404 for non-existent user impersonation', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/admin/users/99999/impersonate')->assertStatus(404);
});

it('audit log includes admin email on start', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)->post("/admin/users/{$user->id}/impersonate");
    $this->artisan('queue:work', ['--once' => true, '--queue' => 'default']);

    $log = AuditLog::where('event', 'admin.impersonation_started')->first();
    expect($log->metadata['admin_email'])->toBe($admin->email);
    expect($log->metadata['target_email'])->toBe($user->email);
});
