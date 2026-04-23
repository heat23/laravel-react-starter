<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase-2 drop of columns whose phase-1 (stop-using) deploy landed in the
 * Sitting-7/8 refactors. The scoring / UTM / lead-qualification pipelines
 * were removed from app code; these columns have no readers or writers.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $columns = [
        'health_score',
        'engagement_score',
        'scores_computed_at',
        'lead_score',
        'lead_qualified_at',
        'sql_qualified_at',
        'marketing_opt_out',
        'acquisition_channel',
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ];

    /**
     * Columns that carry an index (via ->index() in the original create migrations).
     * Indexes must be dropped before the columns on SQLite.
     *
     * @var list<string>
     */
    private array $indexedColumns = [
        'health_score',
        'lead_score',
        'sql_qualified_at',
        'acquisition_channel',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach ($this->indexedColumns as $column) {
                if (Schema::hasColumn('users', $column) && $this->indexExists("users_{$column}_index")) {
                    $table->dropIndex("users_{$column}_index");
                }
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            foreach ($this->columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
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
            if (! Schema::hasColumn('users', 'health_score')) {
                $table->unsignedTinyInteger('health_score')->nullable()->after('utm_campaign')->index();
            }
            if (! Schema::hasColumn('users', 'engagement_score')) {
                $table->unsignedTinyInteger('engagement_score')->nullable()->after('health_score');
            }
            if (! Schema::hasColumn('users', 'scores_computed_at')) {
                $table->timestamp('scores_computed_at')->nullable()->after('engagement_score');
            }
            if (! Schema::hasColumn('users', 'lead_score')) {
                $table->unsignedTinyInteger('lead_score')->nullable()->after('scores_computed_at')->index();
            }
            if (! Schema::hasColumn('users', 'lead_qualified_at')) {
                $table->timestamp('lead_qualified_at')->nullable()->after('lead_score');
            }
            if (! Schema::hasColumn('users', 'sql_qualified_at')) {
                $table->timestamp('sql_qualified_at')->nullable()->after('lead_qualified_at')->index();
            }
            if (! Schema::hasColumn('users', 'marketing_opt_out')) {
                $table->boolean('marketing_opt_out')->default(false)->nullable()->after('lead_qualified_at');
            }
        });
    }

    private function indexExists(string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return DB::selectOne(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND name = ?",
                [$indexName]
            ) !== null;
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            return DB::selectOne(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
                ['users', $indexName]
            ) !== null;
        }

        return true;
    }
};
