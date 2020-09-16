<?php
namespace Give\Revenue\Database\Migrations;

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
	 * Create Meta Tables.
	 *
	 * @since  2.9.0
	 */
	public function up() {
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
}
