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

		add_filter( 'add_post_metadata', array( $this, '__add_meta' ), 0, 5 );
		add_filter( 'get_post_metadata', array( $this, '__get_meta' ), 0, 4 );
		add_filter( 'update_post_metadata', array( $this, '__update_meta' ), 0, 4 );
		add_filter( 'delete_post_metadata', array( $this, '__delete_meta' ), 0, 4 );
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
	public function get_meta( $log_id, $meta_key, $single ) {
		$log_id = $this->sanitize_id( $log_id );

		// Bailout.
		if ( ! $log_id || ! Give()->logs->log_db->is_log( $log_id ) ) {
			return null;
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
		$log_id = $this->sanitize_id( $log_id );
		if ( ! $log_id || ! Give()->logs->log_db->is_log( $log_id ) ) {
			return null;
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
		$log_id = $this->sanitize_id( $log_id );
		if ( ! $log_id || ! Give()->logs->log_db->is_log( $log_id ) ) {
			return null;
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
		$log_id = $this->sanitize_id( $log_id );

		if ( ! $log_id || ! Give()->logs->log_db->is_log( $log_id ) ) {
			return null;
		}

		return delete_metadata( 'log', $log_id, $meta_key, $meta_value );
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
	 * Add support for hidden functions.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		if( ! give_has_upgrade_completed( 'v20_logs_upgrades') ) {
			return;
		}

		switch ( $name ) {
			case '__add_meta':
				$check      = $arguments[0];
				$log_id     = $arguments[1];
				$meta_key   = $arguments[2];
				$meta_value = $arguments[3];
				$unique     = $arguments[4];

				return $this->add_meta( $log_id, $meta_key, $meta_value, $unique );

			case '__get_meta':
				$check    = $arguments[0];
				$log_id   = $arguments[1];
				$meta_key = $arguments[2];
				$single   = $arguments[3];

				return $this->get_meta( $log_id, $meta_key, $single );

			case '__update_meta':
				$check      = $arguments[0];
				$log_id     = $arguments[1];
				$meta_key   = $arguments[2];
				$meta_value = $arguments[3];

				return $this->update_meta( $log_id, $meta_key, $meta_value );

			case '__delete_meta':
				$check      = $arguments[0];
				$log_id     = $arguments[1];
				$meta_key   = $arguments[2];
				$meta_value = $arguments[3];

				return $this->delete_meta( $log_id, $meta_key, $meta_value );
		}
	}
}
