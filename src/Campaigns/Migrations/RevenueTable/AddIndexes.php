<?php

namespace Give\Campaigns\Migrations\RevenueTable;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @since 4.0.0
 */
class AddIndexes extends Migration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'add_indexes_to_revenue_table';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Add indexes to revenue table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2024-10-14 00:00:02');
    }

    /**
     * @inheritDoc
     *
     * @since 4.17.0 Only add indexes that do not exist yet, so re-running the migration is safe.
     * @since 4.0.0
     *
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        try {
            $existing = DB::get_col("SHOW INDEX FROM {$wpdb->give_revenue}", 4);

            $indexes = array_filter(['form_id', 'campaign_id'], static function ($column) use ($existing) {
                return !in_array($column, $existing, true);
            });

            if (!$indexes) {
                return;
            }

            $clauses = array_map(static function ($column) {
                return "ADD INDEX ($column)";
            }, $indexes);

            DB::query("ALTER TABLE {$wpdb->give_revenue} " . implode(', ', $clauses));
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while updating the {$wpdb->give_revenue} table", 0,
                $exception);
        }
    }
}
