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
	 */
	public function run() {
		global $wpdb;

		$charset_collate     = $wpdb->get_charset_collate();
		$tableName           = "{$wpdb->prefix}give_revenue";
		$referencedTableName = "{$wpdb->prefix}posts";

		$sql = "CREATE TABLE {$tableName} (
  			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  			donation_id bigint(20) UNSIGNED NOT NULL,
  			form_id bigint(20) UNSIGNED NOT NULL,
  			amount int UNSIGNED NOT NULL,
  			PRIMARY KEY  (id),
  			FOREIGN KEY (donation_id) REFERENCES {$referencedTableName}(ID) ON DELETE CASCADE,
  			FOREIGN KEY (form_id) REFERENCES {$referencedTableName}(ID)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
