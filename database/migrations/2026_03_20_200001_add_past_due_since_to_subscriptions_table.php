<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subscriptions', 'past_due_since')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->timestamp('past_due_since')->nullable()->after('stripe_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('subscriptions', 'past_due_since')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('past_due_since');
            });
        }
    }
};
