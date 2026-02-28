<?php

use App\Services\AdminBillingStatsService;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    config([
        'plans.pro.stripe_price_monthly' => 'price_pro_monthly',
        'plans.pro.stripe_price_annual' => 'price_pro_annual',
        'plans.pro.price_monthly' => 29,
        'plans.pro.price_annual' => 290,
    ]);
});

it('returns null for unknown stripe price IDs', function () {
    $service = app(BillingService::class);

    expect($service->resolveTierFromPrice('price_unknown_xyz'))->toBeNull();
    expect($service->resolveTierFromPrice('price_pro_monthly'))->toBe('pro');
});

it('does not count unknown prices in MRR calculation', function () {
    // Create a known subscription
    $user = \App\Models\User::factory()->create();

    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'default',
        'stripe_id' => 'sub_known',
        'stripe_status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $knownSubId = DB::getPdo()->lastInsertId();

    DB::table('subscription_items')->insert([
        'subscription_id' => $knownSubId,
        'stripe_id' => 'si_known',
        'stripe_product' => 'prod_test',
        'stripe_price' => 'price_pro_monthly',
        'quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create an unknown-price subscription
    DB::table('subscriptions')->insert([
        'user_id' => $user->id,
        'type' => 'secondary',
        'stripe_id' => 'sub_unknown',
        'stripe_status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $unknownSubId = DB::getPdo()->lastInsertId();

    DB::table('subscription_items')->insert([
        'subscription_id' => $unknownSubId,
        'stripe_id' => 'si_unknown',
        'stripe_product' => 'prod_deleted',
        'stripe_price' => 'price_deleted_tier',
        'quantity' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(AdminBillingStatsService::class);
    $stats = $service->getDashboardStats();

    // MRR should only include the known pro subscription ($29/mo), not the unknown one
    expect((float) $stats['mrr'])->toBe(29.0);
});
