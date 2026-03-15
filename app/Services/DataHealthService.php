<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataHealthService
{
    /**
     * Run all data integrity checks and return results.
     *
     * @return array<string, array{status: string, count: int, description: string}>
     */
    public function runAllChecks(): array
    {
        $checks = [
            'orphaned_tokens' => $this->checkOrphanedTokens(),
            'orphaned_audit_logs' => $this->checkOrphanedAuditLogs(),
        ];

        if (Schema::hasTable('webhook_deliveries')) {
            $checks['stale_webhook_deliveries'] = $this->checkStaleWebhookDeliveries();
        }

        if (Schema::hasTable('subscriptions')) {
            $checks['orphaned_subscriptions'] = $this->checkOrphanedSubscriptions();
        }

        return $checks;
    }

    private function checkOrphanedTokens(): array
    {
        $count = DB::table('personal_access_tokens')
            ->leftJoin('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->where('personal_access_tokens.tokenable_type', 'App\\Models\\User')
            ->whereNull('users.id')
            ->count();

        return [
            'status' => $count === 0 ? 'ok' : 'warning',
            'count' => $count,
            'description' => 'API tokens referencing deleted users',
        ];
    }

    private function checkOrphanedAuditLogs(): array
    {
        $count = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->whereNotNull('audit_logs.user_id')
            ->whereNull('users.id')
            ->count();

        return [
            'status' => $count === 0 ? 'ok' : 'warning',
            'count' => $count,
            'description' => 'Audit log entries referencing deleted users',
        ];
    }

    private function checkStaleWebhookDeliveries(): array
    {
        $count = DB::table('webhook_deliveries')
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subDays(7))
            ->count();

        return [
            'status' => $count === 0 ? 'ok' : 'warning',
            'count' => $count,
            'description' => 'Webhook deliveries stuck in pending for over 7 days',
        ];
    }

    private function checkOrphanedSubscriptions(): array
    {
        $count = DB::table('subscriptions')
            ->leftJoin('users', 'subscriptions.user_id', '=', 'users.id')
            ->whereNull('users.id')
            ->count();

        return [
            'status' => $count === 0 ? 'ok' : 'warning',
            'count' => $count,
            'description' => 'Subscriptions referencing deleted users',
        ];
    }
}
