<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add unique constraint to prevent duplicate active subscriptions.
     *
     * Prevents race conditions where rapid checkout clicks could bypass
     * application-level checks and create duplicate subscriptions.
     * Only one active subscription (ends_at IS NULL) per user+type.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('
                CREATE UNIQUE INDEX subscriptions_unique_active
                ON subscriptions (user_id, type)
                WHERE ends_at IS NULL
            ');
        } elseif ($driver === 'mysql') {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->boolean('is_active_constraint')
                    ->nullable()
                    ->storedAs('CASE WHEN ends_at IS NULL THEN 1 ELSE NULL END')
                    ->after('ends_at');

                $table->unique(['user_id', 'type', 'is_active_constraint'], 'subscriptions_unique_active');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('
                CREATE UNIQUE INDEX subscriptions_unique_active
                ON subscriptions (user_id, type)
                WHERE ends_at IS NULL
            ');
        } else {
            throw new \RuntimeException("Unsupported database driver: {$driver}");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS subscriptions_unique_active');
        } elseif ($driver === 'mysql') {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropUnique('subscriptions_unique_active');
                $table->dropColumn('is_active_constraint');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS subscriptions_unique_active');
        }
    }
};
