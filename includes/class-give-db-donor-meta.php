<?php
/**
 * Donor Meta DB class
 *
 * @package     Give
 * @subpackage  Classes/DB Donor Meta
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_DB_Donor_Meta
 *
 * This class is for interacting with the donor meta database table.
 *
 * @since 1.6
 */
class Give_DB_Donor_Meta extends Give_DB {

	/**
	 * Give_DB_Donor_Meta constructor.
	 *
	 * @access  public
	 * @since   1.6
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$wpdb->customermeta = $this->table_name  = $wpdb->prefix . 'give_customermeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		$this->register_table();
	}

	/**
	 * Get table columns and data types.
	 *
	 * @access  public
	 * @since   1.6
	 *
	 * @return  array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'meta_id'     => '%d',
			'customer_id' => '%d',
			'meta_key'    => '%s',
			'meta_value'  => '%s',
		);
	}

	/**
	 * Retrieve donor meta field for a donor.
	 *
	 * For internal use only. Use Give_Donor->get_meta() for public usage.
	 *
	 * @access  private
	 * @since   1.6
	 *
	 * @param   int    $donor_id Donor ID.
	 * @param   string $meta_key The meta key to retrieve.
	 * @param   bool   $single Whether to return a single value.
	 *
	 * @return  mixed                 Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_meta( $donor_id = 0, $meta_key = '', $single = false ) {
		$donor_id = $this->sanitize_donor_id( $donor_id );
		if ( false === $donor_id ) {
			return false;
		}

		return get_metadata( 'customer', $donor_id, $meta_key, $single );
	}

	/**
	 * Add meta data field to a donor.
	 *
	 * For internal use only. Use Give_Donor->add_meta() for public usage.
	 *
	 * @access  private
	 * @since   1.6
	 *
	 * @param   int    $donor_id Donor ID.
	 * @param   string $meta_key Metadata name.
	 * @param   mixed  $meta_value Metadata value.
	 * @param   bool   $unique Optional, default is false. Whether the same key should not be added.
	 *
	 * @return  bool                  False for failure. True for success.
	 */
	public function add_meta( $donor_id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$donor_id = $this->sanitize_donor_id( $donor_id );
		if ( false === $donor_id ) {
			return false;
		}

		return add_metadata( 'customer', $donor_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update donor meta field based on Donor ID.
	 *
	 * For internal use only. Use Give_Donor->update_meta() for public usage.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and Donor ID.
	 *
	 * If the meta field for the donor does not exist, it will be added.
	 *
	 * @access  private
	 * @since   1.6
	 *
	 * @param   int    $donor_id Donor ID.
	 * @param   string $meta_key Metadata key.
	 * @param   mixed  $meta_value Metadata value.
	 * @param   mixed  $prev_value Optional. Previous value to check before removing.
	 *
	 * @return  bool                  False on failure, true if success.
	 */
	public function update_meta( $donor_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$donor_id = $this->sanitize_donor_id( $donor_id );
		if ( false === $donor_id ) {
			return false;
		}

		return update_metadata( 'customer', $donor_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a donor.
	 *
	 * For internal use only. Use Give_Donor->delete_meta() for public usage.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @access  private
	 * @since   1.6
	 *
	 * @param   int    $donor_id Donor ID.
	 * @param   string $meta_key Metadata name.
	 * @param   mixed  $meta_value Optional. Metadata value.
	 *
	 * @return  bool                  False for failure. True for success.
	 */
	public function delete_meta( $donor_id = 0, $meta_key = '', $meta_value = '' ) {
		return delete_metadata( 'customer', $donor_id, $meta_key, $meta_value );
	}

	/**
	 * Remove all meta data matching criteria from a donor id.
	 *
	 * @access  private
	 * @since   1.8.14
	 *
	 * @param   int    $donor_id Donor ID.
	 *
	 * @return  bool  False for failure. True for success.
	 */
	public function delete_all_meta( $donor_id = 0 ) {
		global $wpdb;
		$wpdb->delete( $this->table_name, array( 'customer_id' => $donor_id ), array( '%d' ) );
	}

	/**
	 * Create the table
	 *
	 * @access public
	 * @since  1.6
	 *
	 * @return void
	 */
	public function create_table() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY customer_id (customer_id),
			KEY meta_key (meta_key)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

	/**
	 * Given a donor ID, make sure it's a positive number, greater than zero before inserting or adding.
	 *
	 * @access private
	 * @since  1.6
	 *
	 * @param  int|stripe $donor_id A passed donor ID.
	 *
	 * @return int|bool                The normalized donor ID or false if it's found to not be valid.
	 */
	private function sanitize_donor_id( $donor_id ) {
		if ( ! is_numeric( $donor_id ) ) {
			return false;
		}

		$donor_id = (int) $donor_id;

		// We were given a non positive number.
		if ( absint( $donor_id ) !== $donor_id ) {
			return false;
		}

		if ( empty( $donor_id ) ) {
			return false;
		}

		return absint( $donor_id );

	}

}
