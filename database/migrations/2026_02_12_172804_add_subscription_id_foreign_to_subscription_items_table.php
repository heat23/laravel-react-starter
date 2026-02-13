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
        if (! Schema::hasTable('subscription_items') || ! Schema::hasTable('subscriptions')) {
            return;
        }

        try {
            Schema::table('subscription_items', function (Blueprint $table) {
                $table->foreign('subscription_id')
                    ->references('id')->on('subscriptions')
                    ->cascadeOnDelete();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Foreign key already exists — safe to ignore
            if (! str_contains($e->getMessage(), 'already exists')) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('subscription_items')) {
            return;
        }

        Schema::table('subscription_items', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
        });
    }
};
