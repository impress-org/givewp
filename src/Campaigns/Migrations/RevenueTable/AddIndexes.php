<?php

namespace Give\Campaigns\Migrations\RevenueTable;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
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
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        try {
            DB::query("ALTER TABLE {$wpdb->give_revenue} ADD INDEX (form_id), ADD INDEX (campaign_id)");
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while updating the {$wpdb->give_revenue} table", 0,
                $exception);
        }
    }
}
