<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    registerAdminRoutes();
});

it('admin can view product analytics page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Admin/ProductAnalytics'));
});

it('requires admin to view product analytics', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/analytics')
        ->assertForbidden();
});

it('unauthenticated user cannot view product analytics', function () {
    $this->get('/admin/analytics')
        ->assertRedirect('/login');
});

it('returns all required analytics props', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/analytics')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/ProductAnalytics')
            ->has('signup_trend')
            ->has('onboarding_funnel')
            ->has('activation')
            ->has('feature_adoption')
            ->has('subscription_events')
        );
});

it('signup_trend is an array', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/analytics');

    $response->assertInertia(function ($page) {
        $props = $page->toArray()['props'];
        expect($props['signup_trend'])->toBeArray();
    });
});

it('activation prop contains total_users, activated_users, and activation_rate', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/analytics');

    $response->assertInertia(fn ($page) => $page
        ->has('activation.total_users')
        ->has('activation.activated_users')
        ->has('activation.activation_rate')
    );
});

it('activation rate reflects correct ratio of activated to total users', function () {
    $admin = User::factory()->admin()->create();
    Cache::flush();

    $response = $this->actingAs($admin)->get('/admin/analytics');

    $response->assertInertia(function ($page) {
        $activation = $page->toArray()['props']['activation'];
        $totalUsers = $activation['total_users'];
        $activatedUsers = $activation['activated_users'];
        $activationRate = $activation['activation_rate'];

        expect($totalUsers)->toBeGreaterThanOrEqual(1);
        expect($activatedUsers)->toBeGreaterThanOrEqual(0);

        // Rate must equal round(activated/total*100, 1) — independently computed
        $expectedRate = $totalUsers > 0 ? round($activatedUsers / $totalUsers * 100, 1) : 0.0;
        expect((float) $activationRate)->toEqual($expectedRate);
    });
});
