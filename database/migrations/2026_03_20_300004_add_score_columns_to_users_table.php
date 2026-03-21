<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'health_score')) {
                $table->unsignedTinyInteger('health_score')->nullable()->after('utm_campaign')->index();
            }
            if (! Schema::hasColumn('users', 'engagement_score')) {
                $table->unsignedTinyInteger('engagement_score')->nullable()->after('health_score');
            }
            if (! Schema::hasColumn('users', 'scores_computed_at')) {
                $table->timestamp('scores_computed_at')->nullable()->after('engagement_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['health_score', 'engagement_score', 'scores_computed_at'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
