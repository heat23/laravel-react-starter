<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'lifecycle_stage')) {
                $table->string('lifecycle_stage', 20)->nullable()->default(null)->after('signup_source')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'lifecycle_stage')) {
                $table->dropColumn('lifecycle_stage');
            }
        });
    }
};
