<?php

namespace Give\Donations\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * The sequential ordering table maps a donation to its serial number, and every legacy payment
 * load looks that number up by payment_id. The table only had a primary key on its own id, so
 * each lookup scanned the whole table: at a million donations the Reports page spent minutes in
 * those scans alone. One index on payment_id turns each lookup into a point read.
 *
 * @since TBD
 */
class AddIndexToSequentialOrderingTable extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'add_index_to_sequential_ordering_table';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Add an index on payment_id to the sequential ordering table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2026-10-07 00:00:00');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'give_sequential_ordering';

        try {
            if ($this->isIndexed($table)) {
                return;
            }

            DB::query("ALTER TABLE {$table} ADD INDEX payment_id (payment_id)");
        } catch (DatabaseQueryException $exception) {
            // Another request may have built the index while this one waited on the table lock.
            if ($this->isIndexed($table)) {
                return;
            }

            throw new DatabaseMigrationException('An error occurred while adding an index to the sequential ordering table', 0, $exception);
        }
    }

    /**
     * Whether any index, whatever its name, covers the payment_id column.
     *
     * @since TBD
     */
    private function isIndexed(string $table): bool
    {
        // SHOW INDEX lists one row per column of each index; column 4 is Column_name.
        return in_array('payment_id', DB::get_col("SHOW INDEX FROM {$table}", 4), true);
    }
}
