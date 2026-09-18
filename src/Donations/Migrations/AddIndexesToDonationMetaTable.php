<?php

namespace Give\Donations\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * The donation meta table only indexes donation_id and meta_key on their own. Every model query
 * joins the table once per meta key, and every filter by value (test mode, campaign, email) has
 * to scan the whole key. Two composite indexes cover both patterns.
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

        try {
            $existing = DB::get_col("SHOW INDEX FROM {$table}", 2);

            $indexes = array_diff_key([
                'donation_meta_key' => 'ADD INDEX donation_meta_key (donation_id, meta_key)',
                'meta_key_value' => 'ADD INDEX meta_key_value (meta_key, meta_value(191))',
            ], array_flip($existing));

            if (!$indexes) {
                return;
            }

            DB::query("ALTER TABLE {$table} " . implode(', ', $indexes));
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while adding indexes to the {$table} table", 0, $exception);
        }
    }
}
