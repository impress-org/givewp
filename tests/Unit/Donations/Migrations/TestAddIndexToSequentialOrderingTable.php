<?php

namespace Give\Tests\Unit\Donations\Migrations;

use Give\Donations\Migrations\AddIndexToSequentialOrderingTable;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;

/**
 * @since 4.20.0
 */
class TestAddIndexToSequentialOrderingTable extends TestCase
{
    /**
     * @since 4.20.0
     */
    public function testAddsThePaymentIdIndex()
    {
        $table = DB::prefix('give_sequential_ordering');

        // Start from the layout the installer created before the index existed.
        if (in_array('payment_id', $this->indexNames($table), true)) {
            DB::query("ALTER TABLE {$table} DROP INDEX payment_id");
        }

        (new AddIndexToSequentialOrderingTable())->run();

        $this->assertContains('payment_id', $this->indexNames($table));
    }

    /**
     * @since 4.20.0
     */
    public function testIsIdempotent()
    {
        $table = DB::prefix('give_sequential_ordering');

        (new AddIndexToSequentialOrderingTable())->run();
        $before = $this->indexNames($table);

        (new AddIndexToSequentialOrderingTable())->run();

        $this->assertSame($before, $this->indexNames($table));
    }

    private function indexNames(string $table): array
    {
        return array_values(array_unique(DB::get_col("SHOW INDEX FROM {$table}", 2)));
    }
}
