<?php

namespace Give\Revenue\Migrations;

use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\Database\DB;
use Give\Helpers\Table;

class CreateRevenueTable extends Migration {
	/**
	 * @inheritDoc
	 *
	 * @since 2.9.0
	 */
	public static function id() {
		return 'create_revenue_table';
	}

	/**
	 * @inheritDoc
	 *
	 * @since 2.9.0
	 */
	public static function timestamp() {
		return strtotime( '2019-09-16' );
	}

	/**
	 * @inheritDoc
	 *
	 * @since 2.9.0
	 * @since 2.9.2 throw an exception if there is a SQL error and add log
	 *
	 * @throws DatabaseMigrationException
	 */
	public function run() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$tableName       = "{$wpdb->prefix}give_revenue";

		$sql = "CREATE TABLE {$tableName} (
  			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  			donation_id bigint UNSIGNED NOT NULL,
  			form_id bigint UNSIGNED NOT NULL,
  			amount int UNSIGNED NOT NULL,
  			PRIMARY KEY  (id)
		) {$charset_collate};";

		try {
			DB::delta( $sql );
		} catch ( DatabaseQueryException $exception ) {
			throw new DatabaseMigrationException( 'An error occurred creating the revenue table: ' . print_r( $exception->getQueryErrors(), true ) );
		}
	}
}
