<?php
/**
 * Logs Meta DB class
 *
 * @package     Give
 * @subpackage  Classes/DB Log Meta
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_DB_Log_Meta
 *
 * This class is for interacting with the log meta database table.
 *
 * @since 2.0
 */
class Give_DB_Log_Meta extends Give_DB_Meta {
	/**
	 * Meta supports.
	 *
	 * @since  2.0
	 * @access protected
	 * @var array
	 */
	protected $supports = array();

	/**
	 * Meta type
	 *
	 * @since  2.0
	 * @access protected
	 * @var bool
	 */
	protected $meta_type = 'log';

	/**
	 * Give_DB_Log_Meta constructor.
	 *
	 * @access  public
	 * @since   2.0
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$wpdb->logmeta     = $this->table_name = $wpdb->prefix . 'give_logmeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		$this->register_table();

		parent::__construct();
	}

	/**
	 * Get table columns and data types.
	 *
	 * @access  public
	 * @since   2.0
	 *
	 * @return  array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'meta_id'    => '%d',
			'log_id'     => '%d',
			'meta_key'   => '%s',
			'meta_value' => '%s',
		);
	}

	/**
	 * Delete all log meta
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param int $log_id
	 *
	 * @return bool
	 */
	public function delete_row( $log_id = 0 ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Row ID must be positive integer
		$log_id = absint( $log_id );

		if ( empty( $log_id ) ) {
			return false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE log_id = %d", $log_id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Create the table
	 *
	 * @access public
	 * @since  2.0
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->logmeta} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			log_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY log_id (log_id),
			KEY meta_key (meta_key)
			) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Check if current id is valid
	 *
	 * @since  2.0
	 * @access protected
	 *
	 * @param $ID
	 *
	 * @return bool
	 */
	protected function is_valid_post_type( $ID ) {
		return $ID && true;
	}

	/**
	 * check if custom meta table enabled or not.
	 *
	 * @since  2.0
	 * @access protected
	 * @return bool
	 */
	protected function is_custom_meta_table_active() {
		return give_has_upgrade_completed( 'v20_logs_upgrades' );
	}
}
