<?php

namespace App\Services\Billing\Stats;

use App\Helpers\QueryHelper;
use Illuminate\Support\Facades\DB;

class GrowthChartCalculator
{
    /** @return array<int, array{date: string, count: int}> */
    public function calculate(): array
    {
        return DB::table('subscriptions')
            ->select(QueryHelper::dateExpression('created_at'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'count' => (int) $row->count])
            ->toArray();
    }
}
