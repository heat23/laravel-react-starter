<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to audit_logs for admin dashboard filtering
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (! Schema::hasIndex('audit_logs', 'audit_logs_event_index')) {
                    $table->index('event');
                }
                if (! Schema::hasIndex('audit_logs', 'audit_logs_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        // Add indexes to webhook_deliveries for status filtering and pagination
        if (Schema::hasTable('webhook_deliveries')) {
            Schema::table('webhook_deliveries', function (Blueprint $table) {
                if (! Schema::hasIndex('webhook_deliveries', 'webhook_deliveries_status_index')) {
                    $table->index('status');
                }
                if (! Schema::hasIndex('webhook_deliveries', 'webhook_deliveries_created_at_index')) {
                    $table->index('created_at');
                }
            });
        }

        // Add index to incoming_webhooks for provider filtering
        if (Schema::hasTable('incoming_webhooks')) {
            Schema::table('incoming_webhooks', function (Blueprint $table) {
                if (! Schema::hasIndex('incoming_webhooks', 'incoming_webhooks_provider_index')) {
                    $table->index('provider');
                }
            });
        }

        // Add composite index to feature_flag_overrides for faster lookups
        if (Schema::hasTable('feature_flag_overrides')) {
            Schema::table('feature_flag_overrides', function (Blueprint $table) {
                if (! Schema::hasIndex('feature_flag_overrides', 'feature_flag_overrides_flag_user_id_index')) {
                    $table->index(['flag', 'user_id']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex(['event']);
                $table->dropIndex(['created_at']);
            });
        }

        if (Schema::hasTable('webhook_deliveries')) {
            Schema::table('webhook_deliveries', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropIndex(['created_at']);
            });
        }

        if (Schema::hasTable('incoming_webhooks')) {
            Schema::table('incoming_webhooks', function (Blueprint $table) {
                $table->dropIndex(['provider']);
            });
        }

        if (Schema::hasTable('feature_flag_overrides')) {
            Schema::table('feature_flag_overrides', function (Blueprint $table) {
                $table->dropIndex(['flag', 'user_id']);
            });
        }
    }
};
