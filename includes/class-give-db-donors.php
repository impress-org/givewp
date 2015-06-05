<?php
/**
 * Customers DB class
 *
 * This class is for interacting with the donors' database table
 *
 * @package     Give
 * @subpackage  Classes/DB Customers
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_DB_Donors Class
 *
 * @since 1.0
 */
class Give_DB_Donors extends Give_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'give_donors';
		$this->primary_key = 'id';
		$this->version     = '1.0';

	}

	/**
	 * Get columns and formats
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_columns() {
		return array(
			'id'             => '%d',
			'user_id'        => '%d',
			'name'           => '%s',
			'email'          => '%s',
			'payment_ids'    => '%s',
			'purchase_value' => '%s',
			'purchase_count' => '%d',
			'notes'          => '%s',
			'date_created'   => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_column_defaults() {
		return array(
			'user_id'        => 0,
			'email'          => '',
			'name'           => '',
			'payment_ids'    => '',
			'purchase_value' => '',
			'purchase_count' => 0,
			'notes'          => '',
			'date_created'   => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Add a donor
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function add( $data = array() ) {

		$defaults = array(
			'payment_ids' => ''
		);

		$args = wp_parse_args( $data, $defaults );

		if ( empty( $args['email'] ) ) {
			return false;
		}

		if ( ! empty( $args['payment_ids'] ) && is_array( $args['payment_ids'] ) ) {
			$args['payment_ids'] = implode( ',', array_unique( array_values( $args['payment_ids'] ) ) );
		}

		$donor = $this->get_by( 'email', $args['email'] );

		if ( $donor ) {
			// update an existing donor

			// Update the payment IDs attached to the donor
			if ( ! empty( $args['payment_ids'] ) ) {

				if ( empty( $donor->payment_ids ) ) {

					$donor->payment_ids = $args['payment_ids'];

				} else {

					$existing_ids          = array_map( 'absint', explode( ',', $donor->payment_ids ) );
					$payment_ids           = array_map( 'absint', explode( ',', $args['payment_ids'] ) );
					$payment_ids           = array_merge( $payment_ids, $existing_ids );
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
	 * Checks if a donor exists by email
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function exists( $email = '' ) {

		return (bool) $this->get_column_by( 'id', 'email', $email );

	}

	/**
	 * Attaches a payment ID to a donor
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function attach_payment( $donor_id = 0, $payment_id = 0 ) {

		$donor = $this->get( $donor_id );

		if ( ! $donor ) {
			return false;
		}

		if ( empty( $donor->payment_ids ) ) {

			$donor->payment_ids = $payment_id;

		} else {

			$payment_ids           = array_map( 'absint', explode( ',', $donor->payment_ids ) );
			$payment_ids[]         = $payment_id;
			$donor->payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

		}

		return $this->update( $donor_id, (array) $donor );

	}

	/**
	 * Removes a payment ID from a donor
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function remove_payment( $donor_id = 0, $payment_id = 0 ) {

		$donor = $this->get( $donor_id );

		if ( ! $donor ) {
			return false;
		}

		if ( ! $payment_id ) {
			return false;
		}

		if ( ! empty( $donor->payment_ids ) ) {

			$payment_ids = array_map( 'absint', explode( ',', $donor->payment_ids ) );

			$pos = array_search( $payment_id, $payment_ids );
			if ( false === $pos ) {
				return false;
			}

			unset( $payment_ids[ $pos ] );
			$payment_ids = array_filter( $payment_ids );

			$donor->payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

		}

		return $this->update( $donor_id, (array) $donor );

	}

	/**
	 * Increments donor purchase stats
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function increment_stats( $donor_id = 0, $amount = 0.00 ) {

		$donor = $this->get( $donor_id );

		if ( ! $donor ) {
			return false;
		}

		$donor->purchase_count = intval( $donor->purchase_count ) + 1;
		$donor->purchase_value = floatval( $donor->purchase_value ) + $amount;

		return $this->update( $donor_id, (array) $donor );

	}

	/**
	 * Decrements donor purchase stats
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function decrement_stats( $donor_id = 0, $amount = 0.00 ) {

		$donor = $this->get( $donor_id );

		if ( ! $donor ) {
			return false;
		}

		$donor->purchase_count = intval( $donor->purchase_count ) - 1;
		$donor->purchase_value = floatval( $donor->purchase_value ) - $amount;

		return $this->update( $donor_id, (array) $donor );

	}

	/**
	 * Retrieve donors from the database
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function get_donors( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'user_id' => 0,
			'orderby' => 'id',
			'order'   => 'DESC'
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = '';

		// specific donors
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', $args['id'] );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= "WHERE `id` IN( {$ids} ) ";

		}

		// donors for specific user accounts
		if ( ! empty( $args['user_id'] ) ) {

			if ( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', $args['user_id'] );
			} else {
				$user_ids = intval( $args['user_id'] );
			}

			$where .= "WHERE `user_id` IN( {$user_ids} ) ";

		}

		//specific donors by email
		if ( ! empty( $args['email'] ) ) {

			if ( is_array( $args['email'] ) ) {
				$emails = "'" . implode( "', '", $args['email'] ) . "'";
			} else {
				$emails = "'" . $args['email'] . "'";
			}

			$where .= "WHERE `email` IN( {$emails} ) ";

		}

		// Customers created for a specific date or in a date range
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					if ( ! empty( $where ) ) {

						$where .= " AND `date_created` >= '{$start}'";

					} else {

						$where .= " WHERE `date_created` >= '{$start}'";

					}

				}

				if ( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					if ( ! empty( $where ) ) {

						$where .= " AND `date_created` <= '{$end}'";

					} else {

						$where .= " WHERE `date_created` <= '{$end}'";

					}

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				if ( empty( $where ) ) {
					$where .= " WHERE";
				} else {
					$where .= " AND";
				}

				$where .= " $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		if ( 'purchase_value' == $args['orderby'] ) {
			$args['orderby'] = 'purchase_value+0';
		}

		$cache_key = md5( 'give_donors_' . serialize( $args ) );

		$donors = wp_cache_get( $cache_key, 'donors' );

		if ( $donors === false ) {
			$donors = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );
			wp_cache_set( $cache_key, $donors, 'donors', 3600 );
		}

		return $donors;

	}


	/**
	 * Count the total number of donors in the database
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$where = '';

		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );
				$end   = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

				if ( empty( $where ) ) {

					$where .= " WHERE `date_created` >= '{$start}' AND `date_created` <= '{$end}'";

				} else {

					$where .= " AND `date_created` >= '{$start}' AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				if ( empty( $where ) ) {
					$where .= " WHERE";
				} else {
					$where .= " AND";
				}

				$where .= " $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}


		$cache_key = md5( 'give_donors_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'donors' );

		if ( $count === false ) {
			$count = $wpdb->get_var( "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};" );
			wp_cache_set( $cache_key, $count, 'donors', 3600 );
		}

		return absint( $count );

	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		email varchar(50) NOT NULL,
		name mediumtext NOT NULL,
		purchase_value mediumtext NOT NULL,
		purchase_count bigint(20) NOT NULL,
		payment_ids longtext NOT NULL,
		notes longtext NOT NULL,
		date_created datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY user (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}