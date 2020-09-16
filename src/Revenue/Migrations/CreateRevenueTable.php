<?php

namespace Give\Revenue\Migrations;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Helpers\Table;

class CreateRevenueTable extends Migration {
	/**
	 * @inheritDoc
	 *
	 * @since 2.9.0
	 */
	public function run() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$tableName       = Table::getName( 'give_revenue' );

		$sql = "CREATE TABLE { $tableName } (
  			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  			donation_id bigint(20) NOT NULL,
  			form_id bigint(20) NOT NULL,
  			amount int UNSIGNED NOT NULL,
  			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * @inheritDoc
	 *
	 * @since 2.9.0
	 */
	public static function timestamp() {
		return strtotime( '2019-09-16' );
	}
}
