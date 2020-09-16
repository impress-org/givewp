<?php
namespace Give\Database\Migrations;

use Give\Framework\Migration;
use Give\Helpers\Table;

/**
 * Class Revenue
 * @package Give\Database\Tables
 *
 * @since 2.9.0
 */
class CreateRevenueTable extends Migration {
	/**
	 * @inheritdoc
	 */
	public static function run() {
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
	 * @inheritdoc
	 */
	public static function timestamp() {
		return '20190916000000';
	}
}
