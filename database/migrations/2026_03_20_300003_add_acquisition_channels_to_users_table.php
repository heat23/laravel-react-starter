<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'acquisition_channel')) {
                $table->string('acquisition_channel', 64)->nullable()->after('lifecycle_stage')->index();
            }
            if (! Schema::hasColumn('users', 'utm_source')) {
                $table->string('utm_source', 128)->nullable()->after('acquisition_channel');
            }
            if (! Schema::hasColumn('users', 'utm_medium')) {
                $table->string('utm_medium', 64)->nullable()->after('utm_source');
            }
            if (! Schema::hasColumn('users', 'utm_campaign')) {
                $table->string('utm_campaign', 128)->nullable()->after('utm_medium');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['acquisition_channel', 'utm_source', 'utm_medium', 'utm_campaign'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
