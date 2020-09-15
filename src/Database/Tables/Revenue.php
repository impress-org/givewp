<?php
namespace Give\Database\Tables;

/**
 * Class Revenue
 * @package Give\Database\Tables
 *
 * @since 2.9.0
 */
class Revenue extends Table {
	/**
	 * @inheritdoc
	 */
	protected $nameSuffix = 'give_revenue';

	/**
	 * @inheritdoc
	 */
	protected $primaryKey = 'id';

	/**
	 * @inheritdoc
	 */
	protected $columns = [
		'id'           => '%d',
		'donation_id'  => '%d',
		'form_id'      => '%d',
		'amount'       => '%d',
		'date_created' => '%s',
	];

	/**
	 * Create Meta Tables.
	 *
	 * @since  2.9.0
	 */
	protected function createTable() {
		$charset_collate = $this->db->get_charset_collate();

		$sql = "CREATE TABLE {$this->getName()} (
  			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  			donation_id bigint(20) NOT NULL,
  			form_id bigint(20) NOT NULL,
  			amount int UNSIGNED NOT NULL,
  			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$status = dbDelta( $sql );

		$this->setVersion();
	}
}
