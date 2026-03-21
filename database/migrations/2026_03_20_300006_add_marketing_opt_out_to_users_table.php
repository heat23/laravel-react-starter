<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'marketing_opt_out')) {
                $table->boolean('marketing_opt_out')->default(false)->nullable()->after('lead_qualified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'marketing_opt_out')) {
                $table->dropColumn('marketing_opt_out');
            }
        });
    }
};
