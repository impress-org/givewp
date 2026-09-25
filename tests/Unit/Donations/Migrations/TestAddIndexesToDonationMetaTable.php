<?php

namespace Give\Tests\Unit\Donations\Migrations;

use Give\Donations\Migrations\AddIndexesToDonationMetaTable;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class TestAddIndexesToDonationMetaTable extends TestCase
{
    /**
     * @since TBD
     */
    public function testReplacesSingleColumnIndexesWithCompositeOnes()
    {
        $table = DB::prefix('give_donationmeta');

        // Start from the legacy layout the installer creates.
        $this->dropIndexIfExists($table, 'donation_meta_key');
        $this->dropIndexIfExists($table, 'meta_key_value');
        $this->addIndexIfMissing($table, 'donation_id', '(donation_id)');
        $this->addIndexIfMissing($table, 'meta_key', '(meta_key(191))');

        (new AddIndexesToDonationMetaTable())->run();

        $indexes = $this->indexNames($table);
        $this->assertContains('donation_meta_key', $indexes);
        $this->assertContains('meta_key_value', $indexes);
        $this->assertNotContains('donation_id', $indexes);
        $this->assertNotContains('meta_key', $indexes);
    }

    /**
     * @since TBD
     */
    public function testIsIdempotent()
    {
        $table = DB::prefix('give_donationmeta');

        (new AddIndexesToDonationMetaTable())->run();
        $before = $this->indexNames($table);

        (new AddIndexesToDonationMetaTable())->run();

        $this->assertSame($before, $this->indexNames($table));
    }

    private function indexNames(string $table): array
    {
        return array_values(array_unique(DB::get_col("SHOW INDEX FROM {$table}", 2)));
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (in_array($index, $this->indexNames($table), true)) {
            DB::query("ALTER TABLE {$table} DROP INDEX {$index}");
        }
    }

    private function addIndexIfMissing(string $table, string $index, string $columns): void
    {
        if (!in_array($index, $this->indexNames($table), true)) {
            DB::query("ALTER TABLE {$table} ADD INDEX {$index} {$columns}");
        }
    }
}
