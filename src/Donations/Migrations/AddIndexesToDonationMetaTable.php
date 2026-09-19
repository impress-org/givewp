<?php

namespace Give\Donations\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * The donation meta table only indexes donation_id and meta_key on their own. Every model query
 * joins the table once per meta key, and every filter by value (test mode, campaign, email) has
 * to scan the whole key. Two composite indexes cover both patterns, and the single-column indexes
 * they start with become redundant, so they are dropped to keep the table from growing by more
 * than about a third. This mirrors the WooCommerce HPOS order meta table, which indexes
 * (meta_key, meta_value) and (order_id, meta_key, meta_value) only.
 *
 * @since TBD
 */
class AddIndexesToDonationMetaTable extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'add_indexes_to_donation_meta_table';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Add composite indexes to the donation meta table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2026-09-18 00:00:00');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'give_donationmeta';
        $wanted = ['donation_meta_key', 'meta_key_value'];

        try {
            $existing = array_flip(DB::get_col("SHOW INDEX FROM {$table}", 2));

            // 191 is the longest utf8mb4 prefix that fits the 767-byte key limit of InnoDB tables still in the
            // COMPACT row format, which is what old installs have. WordPress core uses the same prefix.
            $clauses = array_diff_key([
                'donation_meta_key' => 'ADD INDEX donation_meta_key (donation_id, meta_key(191))',
                'meta_key_value' => 'ADD INDEX meta_key_value (meta_key(191), meta_value(191))',
            ], $existing);

            $clauses += array_intersect_key([
                'donation_id' => 'DROP INDEX donation_id',
                'meta_key' => 'DROP INDEX meta_key',
            ], $existing);

            if (!$clauses) {
                return;
            }

            DB::query("ALTER TABLE {$table} " . implode(', ', $clauses));
        } catch (DatabaseQueryException $exception) {
            // Another request may have built the indexes while this one waited on the table lock.
            if (!array_diff($wanted, DB::get_col("SHOW INDEX FROM {$table}", 2))) {
                return;
            }

            throw new DatabaseMigrationException("An error occurred while adding indexes to the {$table} table", 0, $exception);
        }
    }
}
