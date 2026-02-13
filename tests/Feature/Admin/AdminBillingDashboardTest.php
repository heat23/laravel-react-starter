<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    registerAdminRoutes();
    ensureCashierTablesExist();
});

it('redirects guests to login', function () {
    $this->get('/admin/billing')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/billing')->assertStatus(403);
});

it('loads billing dashboard with stats', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    createSubscription($user);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/billing');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Billing/Dashboard')
        ->has('stats')
        ->where('stats.active_subscriptions', 1)
        ->has('tier_distribution')
        ->has('status_breakdown')
        ->has('growth_chart')
        ->has('trial_stats')
        ->has('recent_events')
    );
});

it('handles empty state with no subscriptions', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/billing');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.active_subscriptions', 0)
        ->where('stats.mrr', 0)
        ->where('stats.churn_rate', 0)
        ->where('stats.trial_conversion_rate', 0)
    );
});

it('counts trialing subscriptions', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    createSubscription($user, [
        'stripe_status' => 'trialing',
        'trial_ends_at' => now()->addDays(7),
    ]);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/billing');

    $response->assertInertia(fn ($page) => $page
        ->where('stats.trialing', 1)
        ->where('stats.active_subscriptions', 0)
    );
});

it('includes tier distribution for active subscriptions', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    createSubscription($user1);
    createTeamSubscription($user2);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/billing');

    $response->assertInertia(fn ($page) => $page
        ->has('tier_distribution')
    );
});

it('includes growth chart data', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    createSubscription($user);

    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/billing');

    $response->assertInertia(fn ($page) => $page
        ->has('growth_chart')
    );
});

it('includes recent billing events from audit logs', function () {
    $admin = User::factory()->admin()->create();
    AuditLog::create([
        'event' => 'billing.subscription.created',
        'user_id' => $admin->id,
        'ip' => '127.0.0.1',
        'metadata' => ['tier' => 'pro'],
    ]);

    $response = $this->actingAs($admin)->get('/admin/billing');

    $response->assertInertia(fn ($page) => $page
        ->has('recent_events', 1)
        ->where('recent_events.0.event', 'billing.subscription.created')
    );
});
