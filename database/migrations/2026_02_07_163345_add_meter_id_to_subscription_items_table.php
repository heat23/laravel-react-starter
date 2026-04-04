<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the meter_id column from subscription_items.
     *
     * Usage-based billing via Stripe Meters was scaffolded but never implemented
     * (no MeterEventService, no meter_id population in BillingService). Removing
     * the dead schema prevents false implication that metered billing is active.
     *
     * down() restores the column to allow rollback to the previous state.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('subscription_items', 'meter_id')) {
            return;
        }

        Schema::table('subscription_items', function (Blueprint $table) {
            $table->dropColumn('meter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('subscription_items', 'meter_id')) {
            return;
        }

        Schema::table('subscription_items', function (Blueprint $table) {
            $table->string('meter_id')->nullable()->after('stripe_price');
        });
    }
};
