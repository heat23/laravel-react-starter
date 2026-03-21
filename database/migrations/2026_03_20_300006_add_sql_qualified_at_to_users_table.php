<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'sql_qualified_at')) {
                $table->timestamp('sql_qualified_at')->nullable()->after('lead_qualified_at')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'sql_qualified_at')) {
                $table->dropColumn('sql_qualified_at');
            }
        });
    }
};
