<?php
/**
 * Donors DB
 *
 * @package     Give
 * @subpackage  Classes/Give_DB_Donors
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_DB_Donors Class
 *
 * This class is for interacting with the donor database table.
 *
 * @since 1.0
 */
class Give_DB_Donors extends Give_DB {

	/**
	 * Give_DB_Donors constructor.
	 *
	 * Set up the Give DB Donor class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$wpdb->donors      = $this->table_name = "{$wpdb->prefix}give_donors";
		$this->primary_key = 'id';
		$this->version     = '1.0';

		$this->bc_200_params();

		parent::__construct();
	}

	/**
	 * Get columns and formats
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'id'              => '%d',
			'user_id'         => '%d',
			'name'            => '%s',
			'email'           => '%s',
			'payment_ids'     => '%s',
			'purchase_value'  => '%f',
			'purchase_count'  => '%d',
			'date_created'    => '%s',
			'token'           => '%s',
			'verify_key'      => '%s',
			'verify_throttle' => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array  Default column values.
	 */
	public function get_column_defaults() {
		return array(
			'user_id'         => 0,
			'email'           => '',
			'name'            => '',
			'payment_ids'     => '',
			'purchase_value'  => 0.00,
			'purchase_count'  => 0,
			'date_created'    => date( 'Y-m-d H:i:s' ),
			'token'           => '',
			'verify_key'      => '',
			'verify_throttle' => '',
		);
	}

	/**
	 * Add a donor
	 *
	 * @param  array $data List of donor data to add.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return int|bool
	 */
	public function add( $data = array() ) {

		$defaults = array(
			'payment_ids' => '',
		);

		$args = wp_parse_args( $data, $defaults );

		if ( empty( $args['email'] ) ) {
			return false;
		}

		if ( ! empty( $args['payment_ids'] ) && is_array( $args['payment_ids'] ) ) {
			$args['payment_ids'] = implode( ',', array_unique( array_values( $args['payment_ids'] ) ) );
		}

		$donor = $this->get_donor_by( 'email', $args['email'] );

		// update an existing donor.
		if ( $donor ) {

			// Update the payment IDs attached to the donor
			if ( ! empty( $args['payment_ids'] ) ) {

				if ( empty( $donor->payment_ids ) ) {

					$donor->payment_ids = $args['payment_ids'];

				} else {

					$existing_ids       = array_map( 'absint', explode( ',', $donor->payment_ids ) );
					$payment_ids        = array_map( 'absint', explode( ',', $args['payment_ids'] ) );
					$payment_ids        = array_merge( $payment_ids, $existing_ids );
					$donor->payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

				}

				$args['payment_ids'] = $donor->payment_ids;

			}

			$this->update( $donor->id, $args );

			return $donor->id;

		} else {

			return $this->insert( $args, 'donor' );

		}

	}


	/**
	 * Update a donor.
	 *
	 *
	 * @param int    $row_id
	 * @param array  $data
	 * @param string $where
	 *
	 * @return bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {

		$status = parent::update( $row_id, $data, $where );

		if ( $status ) {
			Give_Cache::delete_group( $row_id, 'give-donors' );
		}

		return $status;
	}

	/**
	 * Insert a donor.
	 *
	 * @param array  $data
	 * @param string $type
	 *
	 * @return int
	 */
	public function insert( $data, $type = '' ) {
		$donor_id = parent::insert( $data, $type );

		if ( $donor_id ) {
			Give_Cache::delete_group( $donor_id, 'give-donors' );
		}

		return $donor_id;
	}

	/**
	 * Delete a donor.
	 *
	 * NOTE: This should not be called directly as it does not make necessary changes to
	 * the payment meta and logs. Use give_donor_delete() instead.
	 *
	 * @param  bool|string|int $_id_or_email ID or Email of Donor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return bool|int
	 */
	public function delete( $_id_or_email = false ) {

		if ( empty( $_id_or_email ) ) {
			return false;
		}

		$column = is_email( $_id_or_email ) ? 'email' : 'id';
		$donor  = $this->get_donor_by( $column, $_id_or_email );

		if ( $donor->id > 0 ) {

			global $wpdb;

			/**
			 * Deleting the donor meta.
			 *
			 * @since 1.8.14
			 */
			Give()->donor_meta->delete_all_meta( $donor->id );

			// Cache already deleted in delete_all_meta fn.

			return $wpdb->delete( $this->table_name, array( 'id' => $donor->id ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Delete a donor by user ID.
	 *
	 * NOTE: This should not be called directly as it does not make necessary changes to
	 * the payment meta and logs. Use give_donor_delete() instead.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int|bool $user_id
	 *
	 * @return bool|int
	 */
	public function delete_by_user_id( $user_id = false ) {
		global $wpdb;

		if ( empty( $user_id ) ) {
			return false;
		}

		/**
		 * Deleting the donor meta.
		 *
		 * @since 1.8.14
		 */
		$donor = new Give_Donor( $user_id, true );
		if ( ! empty( $donor->id ) ) {
			Give()->donor_meta->delete_all_meta( $donor->id );
		}

		// Cache is already deleted in delete_all_meta fn.

		return $wpdb->delete( $this->table_name, array( 'user_id' => $user_id ), array( '%d' ) );
	}

	/**
	 * Checks if a donor exists
	 *
	 * @param  string $value The value to search for. Default is empty.
	 * @param  string $field The Donor ID or email to search in. Default is 'email'.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return bool          True is exists, false otherwise.
	 */
	public function exists( $value = '', $field = 'email' ) {

		$columns = $this->get_columns();
		if ( ! array_key_exists( $field, $columns ) ) {
			return false;
		}

		return (bool) $this->get_column_by( 'id', $field, $value );

	}

	/**
	 * Attaches a payment ID to a donor
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $donor_id   Donor ID.
	 * @param  int $payment_id Payment ID.
	 *
	 * @return bool
	 */
	public function attach_payment( $donor_id = 0, $payment_id = 0 ) {

		$donor = new Give_Donor( $donor_id );

		if ( empty( $donor->id ) ) {
			return false;
		}

		// Attach the payment, but don't increment stats, as this function previously did not
		return $donor->attach_payment( $payment_id, false );

	}

	/**
	 * Removes a payment ID from a donor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $donor_id   Donor ID.
	 * @param  int $payment_id Payment ID.
	 *
	 * @return bool
	 */
	public function remove_payment( $donor_id = 0, $payment_id = 0 ) {

		$donor = new Give_Donor( $donor_id );

		if ( ! $donor ) {
			return false;
		}

		// Remove the payment, but don't decrease stats, as this function previously did not
		return $donor->remove_payment( $payment_id, false );

	}

	/**
	 * Increments donor's donation stats.
	 *
	 * @access public
	 *
	 * @param int   $donor_id Donor ID.
	 * @param float $amount   THe amount to increase.
	 *
	 * @return bool
	 */
	public function increment_stats( $donor_id = 0, $amount = 0.00 ) {

		$donor = new Give_Donor( $donor_id );

		if ( empty( $donor->id ) ) {
			return false;
		}

		$increased_count = $donor->increase_purchase_count();
		$increased_value = $donor->increase_value( $amount );

		return ( $increased_count && $increased_value ) ? true : false;

	}

	/**
	 * Decrements donor's donation stats.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int   $donor_id Donor ID.
	 * @param  float $amount   Amount.
	 *
	 * @return bool
	 */
	public function decrement_stats( $donor_id = 0, $amount = 0.00 ) {

		$donor = new Give_Donor( $donor_id );

		if ( ! $donor ) {
			return false;
		}

		$decreased_count = $donor->decrease_donation_count();
		$decreased_value = $donor->decrease_value( $amount );

		return ( $decreased_count && $decreased_value ) ? true : false;

	}

	/**
	 * Retrieves a single donor from the database
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $field ID or email. Default is 'id'.
	 * @param  mixed  $value The Customer ID or email to search. Default is 0.
	 *
	 * @return mixed         Upon success, an object of the donor. Upon failure, NULL
	 */
	public function get_donor_by( $field = 'id', $value = 0 ) {
		$value = sanitize_text_field( $value );

		// Bailout.
		if ( empty( $field ) || empty( $value ) ) {
			return null;
		}

		// Verify values.
		if ( 'id' === $field || 'user_id' === $field ) {
			// Make sure the value is numeric to avoid casting objects, for example,
			// to int 1.
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = absint( $value );

			if ( $value < 1 ) {
				return false;
			}

		} elseif ( 'email' === $field ) {

			if ( ! is_email( $value ) ) {
				return false;
			}

			$value = trim( $value );
		}

		// Bailout
		if ( ! $value ) {
			return false;
		}

		// Set query params.
		switch ( $field ) {
			case 'id':
				$args['donor'] = $value;
				break;
			case 'email':
				$args['email'] = $value;
				break;
			case 'user_id':
				$args['user'] = $value;
				break;
			default:
				return false;
		}

		// Get donors.
		$donor = new Give_Donors_Query( $args );

		if ( ! $donor = $donor->get_donors() ) {
			// Look for donor from an additional email.
			$args = array(
				'meta_query' => array(
					array(
						'key'   => 'additional_email',
						'value' => $value,
					),
				),
			);

			$donor = new Give_Donors_Query( $args );
			$donor = $donor->get_donors();

			if ( empty( $donor ) ) {
				return false;
			}
		}

		$donor = current( $donor );

		isset( $donor->id ) && Give_Donors_Query::update_meta_cache( array( $donor->id  ) );

		return $donor;
	}

	/**
	 * This function will return donor details by token id.
	 *
	 * Note: This function is for internal purposes only. Don't use this function as it will be deprecated soon.
	 *
	 * @param int $id Email Access Token ID.
	 *
	 * @since 2.3.1
	 *
	 * @return object
	 */
	public function get_donor_by_token( $id ) {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->donors} WHERE verify_key = %s LIMIT 1", $id )
		);
		return $row;
	}

	/**
	 * Retrieve donors from the database.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args
	 *
	 * @return array|object|null Donors array or object. Null if not found.
	 */
	public function get_donors( $args = array() ) {
		$this->bc_1814_params( $args );

		$donors = new Give_Donors_Query( $args );

		return $donors->get_donors();

	}


	/**
	 * Count the total number of donors in the database
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args
	 *
	 * @return int         Total number of donors.
	 */
	public function count( $args = array() ) {
		$this->bc_1814_params( $args );
		$args['count'] = true;

		$cache_key = md5( 'give_donors_count' . serialize( $args ) );
		$count     = Give_Cache::get_group( $cache_key, 'donors' );

		if ( is_null( $count ) ) {
			$donors = new Give_Donors_Query( $args );
			$count  = $donors->get_donors();

			Give_Cache::set_group( $cache_key, $count, 'donors', 3600 );
		}

		return absint( $count );

	}

	/**
	 * Create the table
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function create_table() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		email varchar(255) NOT NULL,
		name mediumtext NOT NULL,
		purchase_value mediumtext NOT NULL,
		purchase_count bigint(20) NOT NULL,
		payment_ids longtext NOT NULL,
		date_created datetime NOT NULL,
		token VARCHAR(255) CHARACTER SET utf8 NOT NULL,
		verify_key VARCHAR(255) CHARACTER SET utf8 NOT NULL,
		verify_throttle DATETIME NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY user (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version, false );
	}

	/**
	 * Add backward compatibility for old table name
	 *
	 * @since  2.0
	 * @access private
	 * @global wpdb $wpdb
	 */
	private function bc_200_params() {
		/* @var wpdb $wpdb */
		global $wpdb;

		if (
			! give_has_upgrade_completed( 'v20_rename_donor_tables' ) &&
			$wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s", "{$wpdb->prefix}give_customers" ) )
		) {
			$wpdb->donors = $this->table_name = "{$wpdb->prefix}give_customers";
		}
	}

	/**
	 * Add backward compatibility for deprecated param
	 *
	 * @since  1.8.14
	 * @access private
	 *
	 * @param $args
	 */
	private function bc_1814_params( &$args ) {
		// Backward compatibility: user_id
		if ( ! empty( $args['user_id'] ) ) {
			$args['user'] = $args['user_id'];
		}

		// Backward compatibility: id
		if ( ! empty( $args['id'] ) ) {
			$args['donor'] = $args['id'];
		}

		// Backward compatibility: name
		if ( ! empty( $args['name'] ) ) {
			$args['s'] = "name:{$args['name']}";
		}

		// Backward compatibility: date
		// Donors created for a specific date or in a date range.
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {
					$args['date_query']['after'] = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );
				}

				if ( ! empty( $args['date']['end'] ) ) {
					$args['date_query']['before'] = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );
				}

			} else {

				$args['date_query']['year']  = date( 'Y', strtotime( $args['date'] ) );
				$args['date_query']['month'] = date( 'm', strtotime( $args['date'] ) );
				$args['date_query']['day']   = date( 'd', strtotime( $args['date'] ) );
			}
		}
	}
}
