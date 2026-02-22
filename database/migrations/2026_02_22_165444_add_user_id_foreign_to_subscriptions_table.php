<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds referential integrity for subscriptions.user_id.
     * Uses nullOnDelete() instead of cascadeOnDelete() because:
     * - User model uses SoftDeletes (hard deletes are rare)
     * - Cashier needs subscription records to exist for Stripe reconciliation
     * - Orphaned subscriptions (user_id=null) are safer than cascade-deleted ones
     */
    public function up(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            return;
        }

        if ($this->hasForeignKey()) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('subscriptions')) {
            return;
        }

        if (! $this->hasForeignKey()) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }

    private function hasForeignKey(): bool
    {
        $connection = Schema::getConnection();
        $foreignKeys = $connection->getSchemaBuilder()->getForeignKeys('subscriptions');

        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey['columns'] === ['user_id']) {
                return true;
            }
        }

        return false;
    }
};
