<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('webhook_deliveries')) {
            return;
        }

        Schema::table('webhook_deliveries', function (Blueprint $table) {
            if (! Schema::hasColumn('webhook_deliveries', 'event_type')) {
                return;
            }

            $table->index('event_type');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('webhook_deliveries')) {
            return;
        }

        Schema::table('webhook_deliveries', function (Blueprint $table) {
            $table->dropIndex(['event_type']);
        });
    }
};
