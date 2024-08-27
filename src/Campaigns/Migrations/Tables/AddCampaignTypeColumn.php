<?php

namespace Give\Campaigns\Migrations\Tables;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 *
 * Modifies the give_campaigns table by adding the campaign_type column
 */
class AddCampaignTypeColumn extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'give-campaigns-add-campaign-type-column';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Add campaign_type column to give_campaigns table';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2024-08-26 00:00:01');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        try {
            DB::query("ALTER TABLE {$wpdb->give_campaigns} ADD COLUMN `campaign_type` VARCHAR(12) NOT NULL DEFAULT ''");
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while updating the {$wpdb->give_campaigns} table", 0, $exception);
        }
    }
}
