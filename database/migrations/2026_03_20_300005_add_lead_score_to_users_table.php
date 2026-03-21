<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'lead_score')) {
                $table->unsignedTinyInteger('lead_score')->nullable()->after('scores_computed_at')->index();
            }
            if (! Schema::hasColumn('users', 'lead_qualified_at')) {
                $table->timestamp('lead_qualified_at')->nullable()->after('lead_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['lead_score', 'lead_qualified_at'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
