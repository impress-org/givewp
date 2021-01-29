<?php

namespace Give\Log\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;



/**
 * Class CreateNewLogTables
 * @package Give\Log\Migrations
 *
 * @since 2.9.7
 */
class CreateNewLogTable extends Migration {

	/**
	 * @return string
	 */
	public static function id() {
		return 'create_new_log_table';
	}

	/**
	 * @return int
	 */
	public static function timestamp() {
		return strtotime( '2021-01-28 12:00' );
	}


	public function run() {
		global $wpdb;

		$sql = "CREATE TABLE {$wpdb->prefix}give_log (
  			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  			log_type VARCHAR(16) NOT NULL,
  			migration_id VARCHAR(64) NULL,
  			data text NOT NULL,
  			category VARCHAR(64) NOT NULL,
  			source VARCHAR(64) NOT NULL,
  			date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  			PRIMARY KEY  (id),
            KEY log_type (log_type),
            KEY migration_id (migration_id),
            KEY category (category),
            KEY source (source)
		) {$wpdb->get_charset_collate()}";

		try {
			DB::delta( $sql );
		} catch ( DatabaseQueryException $exception ) {
			throw DatabaseMigrationException::fromException( $exception, 'An error occurred while creating the give_log table' );
		}
	}

	/**
	 * Check if give_log table exists
	 *
	 * @return bool
	 */
	public function check() {
		global $wpdb;

		return (bool) DB::query(
			DB::prepare( 'SHOW TABLES LIKE %s', "{$wpdb->prefix}give_log" )
		);
	}
}
