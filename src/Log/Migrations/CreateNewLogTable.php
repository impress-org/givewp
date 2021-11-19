<?php

namespace Give\Log\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;

/**
 * Class CreateNewLogTables
 * @package Give\Log\Migrations
 *
 * @since 2.10.0
 */
class CreateNewLogTable extends Migration
{
    /**
     * @return string
     */
    public static function id()
    {
        return 'create_new_log_table';
    }

    /**
     * @return string
     */
    public static function title()
    {
        return esc_html__('Create new give_log table', 'give');
    }

    /**
     * @return int
     */
    public static function timestamp()
    {
        return strtotime('2021-01-28 12:00');
    }

    public function run()
    {
        global $wpdb;

        $table = "{$wpdb->prefix}give_log";
        $charset = DB::get_charset_collate();

        $sql = "CREATE TABLE {$table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			log_type VARCHAR(16) NOT NULL,
			data text NOT NULL,
			category VARCHAR(64) NOT NULL,
			source VARCHAR(64) NOT NULL,
			date DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY log_type (log_type),
			KEY category (category),
			KEY source (source)
		) {$charset}";

        try {
            DB::delta($sql);
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred while creating the give_log table', 0, $exception);
        }
    }
}
