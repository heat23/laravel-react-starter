<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            // Add composite index for AdminBillingStatsService subquery optimization
            // This index improves performance of correlated subqueries in getFilteredSubscriptions()
            $table->index(['subscription_id', 'id'], 'subscription_items_subscription_id_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->dropIndex('subscription_items_subscription_id_id_index');
        });
    }
};
