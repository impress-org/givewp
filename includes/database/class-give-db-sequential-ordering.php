<?php
/**
 * Sequential Donation DB
 *
 * @package     Give
 * @subpackage  Classes/Give_DB_Sequential_Ordering
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_DB_Sequential_Ordering Class
 *
 * This class is for interacting with the sequential donation database table.
 *
 * @since 2.1.0
 */
class Give_DB_Sequential_Ordering extends Give_DB {

	/**
	 * Give_DB_Sequential_Ordering constructor.
	 *
	 * Set up the Give DB Donor class.
	 *
	 * @since  2.1.0
	 * @access public
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'give_sequential_ordering';
		$this->primary_key = 'id';
		$this->version     = '1.0';

		parent::__construct();
	}

	/**
	 * Get columns and formats
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @return array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'id'         => '%d',
			'payment_id' => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @return array  Default column values.
	 */
	public function get_column_defaults() {
		return array(
			'id'         => 0,
			'payment_id' => '',
		);
	}


	/**
	 * Create the table
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		// Calculate auto increment number.
		$payment_ID = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT ID
				FROM $wpdb->posts
				WHERE post_type=%s
				ORDER By ID desc
				LIMIT 1
				",
				'give_payment'
			)
		);

		$sql = "CREATE TABLE {$this->table_name} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        payment_id bigint(20) NOT NULL,
        PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		if ( ! empty( $payment_ID ) ) {
			$auto_increment = $payment_ID + 1;
			$wpdb->query( "ALTER TABLE {$this->table_name} AUTO_INCREMENT={$auto_increment};" );
			give_update_option( 'sequential-ordering_number', $auto_increment );
		} else {
			give_update_option( 'sequential-ordering_number', 1 );
		}

		update_option( $this->table_name . '_db_version', $this->version, false );
	}


	/**
	 * Get id auto increment next value.
	 *
	 * @since 2.1.0
	 * @return null|string
	 */
	public function get_id_auto_increment_val() {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT AUTO_INCREMENT
				FROM  INFORMATION_SCHEMA.TABLES
				WHERE TABLE_SCHEMA=%s
				AND TABLE_NAME=%s
				",
				DB_NAME,
				$this->table_name
			)
		);
	}
}
