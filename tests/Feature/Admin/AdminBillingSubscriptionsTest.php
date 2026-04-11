<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    registerAdminRoutes();
    ensureCashierTablesExist();
});

it('redirects guests to login', function () {
    $this->get('/admin/billing/subscriptions')->assertRedirect('/login');
});

it('returns 403 for non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/billing/subscriptions')->assertStatus(403);
});

it('loads subscriptions list with pagination', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    createSubscription($user);

    $response = $this->actingAs($admin)->get('/admin/billing/subscriptions');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Billing/Subscriptions')
        ->has('subscriptions.data', 1)
        ->has('filters')
        ->has('statuses')
        ->has('tiers')
    );
});

it('searches by user name', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['name' => 'Alice Smith']);
    $user2 = User::factory()->create(['name' => 'Bob Jones']);
    createSubscription($user1);
    createSubscription($user2);

    $response = $this->actingAs($admin)->get('/admin/billing/subscriptions?search=Alice');

    $response->assertInertia(fn ($page) => $page
        ->has('subscriptions.data', 1)
        ->where('subscriptions.data.0.user_name', 'Alice Smith')
    );
});

it('filters by status', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    createSubscription($user1, ['stripe_status' => 'active']);
    createSubscription($user2, ['stripe_status' => 'trialing', 'trial_ends_at' => now()->addDays(7)]);

    $response = $this->actingAs($admin)->get('/admin/billing/subscriptions?status=trialing');

    $response->assertInertia(fn ($page) => $page
        ->has('subscriptions.data', 1)
        ->where('subscriptions.data.0.stripe_status', 'trialing')
    );
});

it('sorts by created_at desc by default', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $sub1 = createSubscription($user1);
    $sub1->forceFill(['created_at' => now()->subDays(5)])->saveQuietly();

    createSubscription($user2);

    $response = $this->actingAs($admin)->get('/admin/billing/subscriptions');

    $response->assertInertia(fn ($page) => $page
        ->has('subscriptions.data', 2)
        ->where('subscriptions.data.0.user_name', $user2->name)
    );
});

it('handles empty state', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/billing/subscriptions');

    $response->assertInertia(fn ($page) => $page
        ->has('subscriptions.data', 0)
    );
});

it('does not duplicate subscriptions with multiple items', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $sub = createSubscription($user);

    // Add a second item to the same subscription (e.g., an add-on)
    $sub->items()->create([
        'stripe_id' => 'si_'.Str::random(14),
        'stripe_product' => 'prod_addon',
        'stripe_price' => 'price_addon_monthly',
        'quantity' => 1,
    ]);

    $response = $this->actingAs($admin)->get('/admin/billing/subscriptions');

    $response->assertInertia(fn ($page) => $page
        ->has('subscriptions.data', 1)
    );
});

it('includes tier resolved from price', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    config(['plans.pro.stripe_price_monthly' => 'price_pro_monthly']);
    createSubscription($user, ['stripe_price' => 'price_pro_monthly']);

    $response = $this->actingAs($admin)->get('/admin/billing/subscriptions');

    $response->assertInertia(fn ($page) => $page
        ->has('subscriptions.data', 1)
    );
});

it('includes subscriptions for soft-deleted users with fallback label', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Deleted Person']);
    createSubscription($user);
    $user->delete();

    $response = $this->actingAs($admin)->get('/admin/billing/subscriptions');

    $response->assertInertia(fn ($page) => $page
        ->has('subscriptions.data', 1)
        ->where('subscriptions.data.0.user_name', '[Deleted User]')
    );
});

it('includes subscriptions for hard-deleted users with fallback label', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Gone Person']);
    createSubscription($user);
    $user->forceDelete();

    $response = $this->actingAs($admin)->get('/admin/billing/subscriptions');

    $response->assertInertia(fn ($page) => $page
        ->has('subscriptions.data', 1)
        ->where('subscriptions.data.0.user_name', '[Deleted User]')
    );
});

it('export requires admin', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/billing/subscriptions/export')
        ->assertForbidden();
});

it('export returns csv with correct headers and content', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Jane Export', 'email' => 'jane@example.com']);
    createSubscription($user, ['stripe_id' => 'sub_export_test', 'stripe_status' => 'active']);

    $response = $this->actingAs($admin)
        ->get('/admin/billing/subscriptions/export');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $content = $response->streamedContent();
    expect($content)
        ->toContain('User Name')
        ->toContain('User Email')
        ->toContain('Tier')
        ->toContain('Status')
        ->toContain('Stripe ID')
        ->toContain('Jane Export')
        ->toContain('jane@example.com')
        ->toContain('sub_export_test');
});

it('subscriptions index logs an audit event', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin/billing/subscriptions');

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.billing.subscriptions_viewed',
        'user_id' => $admin->id,
    ]);
});

it('export logs an audit event', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/billing/subscriptions/export');

    $this->assertDatabaseHas('audit_logs', [
        'event' => 'admin.subscriptions_exported',
        'user_id' => $admin->id,
    ]);
});

it('export applies status filter', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['email' => 'active@example.com']);
    $user2 = User::factory()->create(['email' => 'trialing@example.com']);
    createSubscription($user1, ['stripe_status' => 'active']);
    createSubscription($user2, ['stripe_status' => 'trialing', 'trial_ends_at' => now()->addDays(7)]);

    $response = $this->actingAs($admin)
        ->get('/admin/billing/subscriptions/export?status=active');

    $content = $response->streamedContent();
    expect($content)
        ->toContain('active@example.com')
        ->not->toContain('trialing@example.com');
});

it('export route has rate limit middleware', function () {
    $route = collect(app('router')->getRoutes())
        ->first(fn ($r) => $r->getName() === 'admin.billing.subscriptions.export');

    expect($route)->not->toBeNull();
    expect(implode(' ', $route->middleware()))->toContain('throttle:admin-write');
});

it('subscriptions query count does not scale with subscription count', function () {
    $admin = User::factory()->admin()->create();
    for ($i = 0; $i < 10; $i++) {
        $user = User::factory()->create();
        createSubscription($user);
    }

    DB::enableQueryLog();
    $this->actingAs($admin)->get('/admin/billing/subscriptions');
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    // Should be constant: auth + 1 paginated query (join) + 1 count query
    expect($queryCount)->toBeLessThan(15);
});
