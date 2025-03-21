<?php

namespace Give\Campaigns\Migrations\RevenueTable;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 */
class AddCampaignID extends Migration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'add_campaign_id_to_revenue_table';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Add Campaign ID to revenue table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2024-10-14 00:00:00');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        $table = DB::prefix('give_revenue');
        $columnAdded = maybe_add_column($table, 'campaign_id', "ALTER TABLE $table ADD COLUMN campaign_id INT UNSIGNED NULL");

        if ( ! $columnAdded) {
            throw new DatabaseMigrationException("An error occurred while updating the $table table");
        }
    }
}
