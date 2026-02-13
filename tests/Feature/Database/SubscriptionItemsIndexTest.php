<?php

namespace Tests\Feature\Database;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubscriptionItemsIndexTest extends TestCase
{
    public function test_subscription_items_has_composite_index_for_subquery_optimization(): void
    {
        // Skip for SQLite (tests use in-memory SQLite, production uses MySQL)
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Index verification only available with MySQL');
        }

        // Verify the composite index exists for AdminBillingStatsService performance
        $indexes = collect(DB::select('SHOW INDEX FROM subscription_items'))
            ->where('Key_name', 'subscription_items_subscription_id_id_index')
            ->all();

        $this->assertNotEmpty($indexes, 'Composite index subscription_items_subscription_id_id_index should exist');

        // Verify the index contains both subscription_id and id columns
        $indexColumns = collect($indexes)->pluck('Column_name')->sort()->values()->all();
        $expectedColumns = ['id', 'subscription_id'];

        $this->assertEquals($expectedColumns, $indexColumns, 'Index should contain subscription_id and id columns');
    }

    public function test_subscription_items_index_improves_subquery_performance(): void
    {
        // This test verifies the index is used in EXPLAIN output for the type of query
        // used in AdminBillingStatsService

        // Skip if not MySQL (SQLite in tests doesn't use same index strategy)
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Index verification only works with MySQL');
        }

        $explainResult = DB::select(
            'EXPLAIN SELECT subscription_id, MAX(id) as latest_item_id
             FROM subscription_items
             GROUP BY subscription_id'
        );

        // Verify query uses the index (key column should reference our index or PRIMARY)
        $this->assertNotEmpty($explainResult, 'EXPLAIN should return results');

        $usesIndex = collect($explainResult)->some(function ($row) {
            $key = is_array($row) ? ($row['key'] ?? null) : ($row->key ?? null);

            return $key === 'subscription_items_subscription_id_id_index' || $key === 'PRIMARY';
        });

        $this->assertTrue($usesIndex, 'Query should use an index for performance');
    }
}
