<?php

namespace Give\Campaigns\Migrations\Tables;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 * Creates give_campaign_forms table
 */
class CreateCampaignFormsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'give-campaigns-create-give-campaign-forms-table';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Create give_campaign_forms table';
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
    public function run(): void
    {
        global $wpdb;

        $table = $wpdb->give_campaign_forms;
        $charset = DB::get_charset_collate();

        $sql = "CREATE TABLE $table (
            campaign_id INT UNSIGNED NOT NULL,
            form_id INT UNSIGNED NOT NULL,
            PRIMARY KEY  (campaign_id),
            KEY form_id (form_id)
        ) $charset";

        try {
            DB::delta($sql);
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while creating the $table table", 0, $exception);
        }
    }
}
