<?php

namespace App\Services\Billing\Stats;

use Illuminate\Support\Facades\DB;

class StatusBreakdownCalculator
{
    /** @return array<int, array{status: string, count: int}> */
    public function calculate(): array
    {
        return DB::table('subscriptions')
            ->select('stripe_status', DB::raw('COUNT(*) as count'))
            ->groupBy('stripe_status')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->stripe_status,
                'count' => (int) $row->count,
            ])
            ->toArray();
    }
}
