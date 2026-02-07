<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add webhook sequence tracking to prevent out-of-order event processing.
     *
     * Stripe webhooks can arrive out of order due to retries or network issues.
     * This column stores the timestamp of the last processed webhook event,
     * allowing rejection of events older than the last processed one.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('last_webhook_at')->nullable()->after('ends_at');
            $table->index('last_webhook_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['last_webhook_at']);
            $table->dropColumn('last_webhook_at');
        });
    }
};
