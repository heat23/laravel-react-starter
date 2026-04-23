<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('requires authentication', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('renders dashboard for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')->assertOk()
        ->assertInertia(fn ($page) => $page->component('App/Dashboard'));
});

it('shows the correct plan name for a subscribed user', function () {
    $priceId = 'price_test_pro_monthly';
    config(['plans.pro.stripe_price_monthly' => $priceId]);

    $user = User::factory()->create();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_test',
        'stripe_status' => 'active',
        'stripe_price' => $priceId,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('stats.plan_name', 'Pro'));
});

it('shows Subscribed when price id does not match any plan config', function () {
    $user = User::factory()->create();
    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_unknown',
        'stripe_status' => 'active',
        'stripe_price' => 'price_unknown_xyz',
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('stats.plan_name', 'Subscribed'));
});

it('loads dashboard within the query budget', function () {
    $user = User::factory()->create();

    DB::enableQueryLog();
    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Budget updated to 12: original 9 + login_streak (AuditLog query) + limit_warnings (tokens count)
    // + has_unread_changelog (UserSetting lookup) added by growth audit session.
    expect($queryCount)->toBeLessThanOrEqual(12);
});
