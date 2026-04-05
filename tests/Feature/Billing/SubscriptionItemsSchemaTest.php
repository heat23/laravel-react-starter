<?php

use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
});

it('subscription_items table does not contain the unwired meter_id column', function () {
    expect(Schema::hasColumn('subscription_items', 'meter_id'))->toBeFalse();
});

it('subscription_items table does not contain the unwired meter_event_name column', function () {
    expect(Schema::hasColumn('subscription_items', 'meter_event_name'))->toBeFalse();
});

it('subscription_items table retains all standard cashier columns', function () {
    $requiredColumns = ['id', 'subscription_id', 'stripe_id', 'stripe_product', 'stripe_price', 'quantity', 'created_at', 'updated_at'];

    foreach ($requiredColumns as $column) {
        expect(Schema::hasColumn('subscription_items', $column))
            ->toBeTrue("Expected column '{$column}' to exist on subscription_items");
    }
});
