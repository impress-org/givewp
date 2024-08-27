<?php

namespace Give\Campaigns\Migrations\Tables;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * @unreleased
 * Creates give_campaigns table
 */
class CreateCampaignsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'give-campaigns-create-give-campaigns-table';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'Create give_campaigns table from core';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2024-08-26 00:00:00');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'give_campaigns';
        $charset = DB::get_charset_collate();

        $sql = "CREATE TABLE $table (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			campaign_page_id INT UNSIGNED NULL,
			form_id INT NOT NULL,
			campaign_title TEXT NOT NULL,
			campaign_url TEXT NOT NULL,
			short_desc TEXT NOT NULL,
			long_desc TEXT NOT NULL,
			campaign_logo TEXT NOT NULL,
			campaign_image TEXT NOT NULL,
			primary_color VARCHAR(7) NOT NULL,
			secondary_color VARCHAR(7) NOT NULL,
			campaign_goal INT UNSIGNED NOT NULL,
			status VARCHAR(12) NOT NULL,
			start_date DATETIME NULL,
			end_date DATETIME NULL,
			date_created DATETIME NOT NULL,
			PRIMARY KEY  (id)
		) $charset";

        try {
            DB::delta($sql);
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while creating the $table table", 0, $exception);
        }
    }
}
