<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'last_active_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable()->after('last_login_at');
            $table->index('last_active_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'last_active_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['last_active_at']);
            $table->dropColumn('last_active_at');
        });
    }
};
