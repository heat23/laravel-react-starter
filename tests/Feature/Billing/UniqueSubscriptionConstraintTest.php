<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    config(['features.billing.coming_soon' => false]);
    ensureCashierTablesExist();
    registerBillingRoutes();
});

it('prevents duplicate active subscriptions at database level', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $priceId = config('plans.tiers.pro.stripe_price_id');

    // Create first active subscription
    DB::table('subscriptions')->insert([
        'billable_type' => User::class,
        'billable_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_1',
        'stripe_status' => 'active',
        'stripe_price' => $priceId,
        'quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Attempt to create duplicate active subscription (bypassing application validation)
    expect(function () use ($user, $priceId) {
        DB::table('subscriptions')->insert([
            'billable_type' => User::class,
            'billable_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_test_2',
            'stripe_status' => 'active',
            'stripe_price' => $priceId,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);

    // Verify only one active subscription exists
    $activeCount = DB::table('subscriptions')
        ->where('billable_id', $user->id)
        ->whereNull('ends_at')
        ->count();

    expect($activeCount)->toBe(1);
});

it('allows multiple subscriptions when previous ones are canceled', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $priceId = config('plans.tiers.pro.stripe_price_id');

    // Create first subscription and cancel it
    DB::table('subscriptions')->insert([
        'billable_type' => User::class,
        'billable_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_1',
        'stripe_status' => 'canceled',
        'stripe_price' => $priceId,
        'quantity' => 1,
        'ends_at' => now()->addDays(7), // Grace period
        'created_at' => now()->subDays(30),
        'updated_at' => now(),
    ]);

    // Create new active subscription - should succeed
    DB::table('subscriptions')->insert([
        'billable_type' => User::class,
        'billable_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_2',
        'stripe_status' => 'active',
        'stripe_price' => $priceId,
        'quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Verify both subscriptions exist
    $totalCount = DB::table('subscriptions')
        ->where('billable_id', $user->id)
        ->count();

    expect($totalCount)->toBe(2);
});

it('allows active subscription after previous one fully expired', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $priceId = config('plans.tiers.pro.stripe_price_id');

    // Create first subscription that has fully expired
    DB::table('subscriptions')->insert([
        'billable_type' => User::class,
        'billable_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_1',
        'stripe_status' => 'canceled',
        'stripe_price' => $priceId,
        'quantity' => 1,
        'ends_at' => now()->subDay(), // Already expired
        'created_at' => now()->subDays(30),
        'updated_at' => now()->subDays(8),
    ]);

    // Create new active subscription - should succeed because previous has ends_at set
    DB::table('subscriptions')->insert([
        'billable_type' => User::class,
        'billable_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_2',
        'stripe_status' => 'active',
        'stripe_price' => $priceId,
        'quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Verify both subscriptions exist
    $totalCount = DB::table('subscriptions')
        ->where('billable_id', $user->id)
        ->count();

    expect($totalCount)->toBe(2);

    // Verify only one active (ends_at NULL)
    $activeCount = DB::table('subscriptions')
        ->where('billable_id', $user->id)
        ->whereNull('ends_at')
        ->count();

    expect($activeCount)->toBe(1);
});

it('allows different users to have active subscriptions simultaneously', function () {
    $user1 = User::factory()->create(['email_verified_at' => now()]);
    $user2 = User::factory()->create(['email_verified_at' => now()]);
    $priceId = config('plans.tiers.pro.stripe_price_id');

    // Create active subscription for user 1
    DB::table('subscriptions')->insert([
        'billable_type' => User::class,
        'billable_id' => $user1->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_1',
        'stripe_status' => 'active',
        'stripe_price' => $priceId,
        'quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create active subscription for user 2 - should succeed (different user)
    DB::table('subscriptions')->insert([
        'billable_type' => User::class,
        'billable_id' => $user2->id,
        'type' => 'default',
        'stripe_id' => 'sub_test_2',
        'stripe_status' => 'active',
        'stripe_price' => $priceId,
        'quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Verify both users have one active subscription
    expect(DB::table('subscriptions')->where('billable_id', $user1->id)->whereNull('ends_at')->count())->toBe(1);
    expect(DB::table('subscriptions')->where('billable_id', $user2->id)->whereNull('ends_at')->count())->toBe(1);
});
