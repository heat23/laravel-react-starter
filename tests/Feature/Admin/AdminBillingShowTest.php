<?php

use App\Models\AuditLog;
use App\Models\User;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    registerAdminRoutes();
    ensureCashierTablesExist();
});

it('redirects guests to login', function () {
    $this->get('/admin/billing/subscriptions/1')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();
    $sub = createSubscription($user);

    $this->actingAs($user)->get("/admin/billing/subscriptions/{$sub->id}")->assertStatus(403);
});

it('shows subscription detail', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $sub = createSubscription($user);

    $response = $this->actingAs($admin)->get("/admin/billing/subscriptions/{$sub->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Billing/Show')
        ->has('subscription')
        ->where('subscription.id', $sub->id)
        ->where('subscription.user_name', $user->name)
        ->where('subscription.user_email', $user->email)
        ->where('subscription.stripe_status', 'active')
        ->has('items')
        ->has('audit_logs')
    );
});

it('includes subscription items', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $sub = createSubscription($user);

    $response = $this->actingAs($admin)->get("/admin/billing/subscriptions/{$sub->id}");

    $response->assertInertia(fn ($page) => $page
        ->has('items', 1)
    );
});

it('includes billing audit logs for the user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $sub = createSubscription($user);

    AuditLog::create([
        'event' => 'billing.subscription.created',
        'user_id' => $user->id,
        'ip' => '127.0.0.1',
        'metadata' => [],
    ]);

    $response = $this->actingAs($admin)->get("/admin/billing/subscriptions/{$sub->id}");

    $response->assertInertia(fn ($page) => $page
        ->has('audit_logs', 1)
        ->where('audit_logs.0.event', 'billing.subscription.created')
    );
});

it('shows subscription detail when owner is soft-deleted', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $sub = createSubscription($user);
    $user->delete();

    $response = $this->actingAs($admin)->get("/admin/billing/subscriptions/{$sub->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Billing/Show')
        ->where('subscription.user_name', $user->name)
        ->where('subscription.user_email', $user->email)
    );
});

it('shows subscription detail when owner is hard-deleted', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $sub = createSubscription($user);
    $user->forceDelete();

    $response = $this->actingAs($admin)->get("/admin/billing/subscriptions/{$sub->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Billing/Show')
        ->where('subscription.user_name', '[Deleted User]')
        ->where('subscription.user_email', '')
    );
});

it('returns 404 for non-existent subscription', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/billing/subscriptions/999')->assertStatus(404);
});
