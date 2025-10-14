<?php

namespace Give\Subscriptions\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @since 4.11.0
 */
class AddCampaignIdColumn extends Migration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'add_campaign_id_to_subscriptions_table';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Add Campaign ID to subscriptions table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-10-01 00:00:00');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        $table = DB::prefix('give_subscriptions');
        $columnAdded = maybe_add_column($table, 'campaign_id', "ALTER TABLE $table ADD COLUMN campaign_id INT UNSIGNED NULL");

        if ( ! $columnAdded) {
            throw new DatabaseMigrationException("An error occurred while updating the $table table");
        }
    }
}
