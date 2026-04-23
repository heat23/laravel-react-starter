<?php

namespace App\Services\Billing\Stats;

use Illuminate\Support\Facades\DB;

class ChurnBreakdownCalculator
{
    /** @return array{voluntary: int, involuntary: int} */
    public function calculate(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        // Keep both canonical and legacy event names until a production backfill
        // migrates 'stripe.subscription.deleted' rows to 'subscription.canceled'.
        // Dropping the legacy name silently excludes pre-migration audit rows.
        $rows = DB::table('audit_logs')
            ->whereIn('event', ['subscription.canceled', 'stripe.subscription.deleted'])
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->select('metadata')
            ->get();

        $voluntary = 0;
        $involuntary = 0;

        foreach ($rows as $row) {
            $meta = is_string($row->metadata) ? json_decode($row->metadata, true) : (array) $row->metadata;
            $churnType = $meta['churn_type'] ?? null;

            if ($churnType === 'voluntary') {
                $voluntary++;
            } elseif ($churnType === 'involuntary') {
                $involuntary++;
            }
        }

        return compact('voluntary', 'involuntary');
    }
}
