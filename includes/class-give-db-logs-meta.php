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
class Give_DB_Log_Meta extends Give_DB {

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
	 * Register the table with $wpdb so the metadata api can find it.
	 *
	 * @access  public
	 * @since   2.0
	 *
	 * @return  void
	 */
	public function register_table() {
		global $wpdb;
		$wpdb->logmeta = $this->table_name;
	}

	/**
	 * Retrieve log meta field for a log.
	 *
	 * @access  private
	 * @since   2.0
	 *
	 * @param   int    $log_id   Log ID.
	 * @param   string $meta_key The meta key to retrieve.
	 * @param   bool   $single   Whether to return a single value.
	 *
	 * @return  mixed                 Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_meta( $log_id = 0, $meta_key = '', $single = false ) {
		$log_id = $this->sanitize_log_id( $log_id );
		if ( false === $log_id ) {
			return false;
		}

		return get_metadata( 'log', $log_id, $meta_key, $single );
	}

	/**
	 * Add meta data field to a log.
	 *
	 * @access  private
	 * @since   2.0
	 *
	 * @param   int    $log_id     Log ID.
	 * @param   string $meta_key   Metadata name.
	 * @param   mixed  $meta_value Metadata value.
	 * @param   bool   $unique     Optional, default is false. Whether the same key should not be added.
	 *
	 * @return  bool                  False for failure. True for success.
	 */
	public function add_meta( $log_id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$log_id = $this->sanitize_log_id( $log_id );
		if ( false === $log_id ) {
			return false;
		}

		return add_metadata( 'log', $log_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update log meta field based on Log ID.
	 *
	 * @access  private
	 * @since   2.0
	 *
	 * @param   int    $log_id     Log ID.
	 * @param   string $meta_key   Metadata key.
	 * @param   mixed  $meta_value Metadata value.
	 * @param   mixed  $prev_value Optional. Previous value to check before removing.
	 *
	 * @return  bool                  False on failure, true if success.
	 */
	public function update_meta( $log_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$log_id = $this->sanitize_log_id( $log_id );
		if ( false === $log_id ) {
			return false;
		}

		return update_metadata( 'log', $log_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a log.
	 *
	 * For internal use only. Use Give_Log->delete_meta() for public usage.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @access  private
	 * @since   2.0
	 *
	 * @param   int    $log_id     Log ID.
	 * @param   string $meta_key   Metadata name.
	 * @param   mixed  $meta_value Optional. Metadata value.
	 *
	 * @return  bool                  False for failure. True for success.
	 */
	public function delete_meta( $log_id = 0, $meta_key = '', $meta_value = '' ) {
		return delete_metadata( 'log', $log_id, $meta_key, $meta_value );
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

		$sql = "CREATE TABLE {$this->table_name} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			log_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY log_id (log_id),
			KEY meta_key (meta_key)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Given a log ID, make sure it's a positive number, greater than zero before inserting or adding.
	 *
	 * @access private
	 * @since  2.0
	 *
	 * @param  int|stripe $log_id A passed log ID.
	 *
	 * @return int|bool                The normalized log ID or false if it's found to not be valid.
	 */
	private function sanitize_log_id( $log_id ) {
		if ( ! is_numeric( $log_id ) ) {
			return false;
		}

		$log_id = (int) $log_id;

		// We were given a non positive number.
		if ( absint( $log_id ) !== $log_id ) {
			return false;
		}

		if ( empty( $log_id ) ) {
			return false;
		}

		return absint( $log_id );

	}

}
