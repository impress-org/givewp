<?php
/**
 * Delete Donors.
 *
 * This class handles batch processing of deleting donor data.
 *
 * @subpackage  Admin/Tools/Give_Tools_Delete_Donors
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.8
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Give_Tools_Delete_Test_Transactions Class
 *
 * @since 1.8.8
 */
class Give_Tools_Delete_Donors extends Give_Batch_Export {

	var $request;

	var $key = 'give_temp_delete_donation_ids';

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-form batch processing to be run.
	 * @since  1.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  1.5
	 * @var integer
	 */
	public $per_step = 30;

	public $donor_ids = array();

	/**
	 * Pre Fetch
	 */
	public function pre_fetch() {

		$items = array();
		if ( 1 == $this->step ) {

			$this->delete_data( $this->key );

			$args = apply_filters( 'give_tools_reset_stats_total_args', array(
				'post_type'      => 'give_payment',
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				// ONLY TEST MODE TRANSACTIONS!!!
				'meta_key'   => '_give_payment_mode',
				'meta_value' => 'test'
			) );

			$donation_posts = get_posts( $args );
			foreach ( $donation_posts as $donation ) {
				$items[ $donation->post_author ][] = $donation->ID;
			}
			$this->store_data( $this->key, $items );
		}
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {
		if ( 1 == $this->step ) {
			return 30;
		} elseif (  2 == $this->step ) {
			return 70;
		} else{
			return 100;
		}
	}

	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to delete test transactions.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		if ( 1 == $this->step ) {
			return true;
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;
			return true;
		} else {
			update_option( 'give_earnings_total', give_get_total_earnings( true ) );
			Give_Cache::delete( Give_Cache::get_key('give_estimated_monthly_stats' ) );

			$this->delete_data( $this->key );

			// Reset the sequential order numbers
			if ( give_get_option( 'enable_sequential' ) ) {
				delete_option( 'give_last_payment_number' );
			}

			$this->done    = true;
			$this->message = __( 'Test donor and transactions successfully deleted.', 'give' );
			return false;
		}
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.5
	 * @global object $wpdb Used to query the database using the WordPress Database API
	 *
	 * @return array|bool $data The data for the CSV file
	 */
	public function get_data() {

		$items = $this->get_stored_data( $this->key );

		if ( ! is_array( $items ) ) {
			$this->is_empty = true;
			return false;
		}

		if ( 2 == $this->step ) {
			foreach ( $items as $item ) {
				foreach ( (array)$item as $value ) {
					wp_delete_post( $value, true );
				}
			}
		}

		if ( 3 == $this->step ) {
			foreach ( $items as $key => $value ) {
				$this->donor_ids[] = (int) $key;
			}

			$args = apply_filters( 'give_tools_reset_stats_total_args', array(
				'post_type'      => 'give_payment',
				'post_status'    => 'any',
				'posts_per_page' =>  -1,
				// ONLY TEST MODE TRANSACTIONS!!!
				'meta_key'   => '_give_payment_mode',
				'meta_value' => 'live',
				'author__in'     => $this->donor_ids
			) );

			$donor_ids = array();
			$donation_posts = get_posts( $args );
			foreach ( $donation_posts as $donation ) {
				$donor_ids[] = (int) $donation->post_author;
			}
			$donor_ids = array_unique( $donor_ids );
			$delete_donors = array_diff( $this->donor_ids, $donor_ids );

			foreach ( $delete_donors as $donor ) {
				Give()->donors->delete_by_user_id( $donor );
			}
			return false;
		}
		return true;
	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  1.5
	 *
	 * @param  string $key The option_name
	 *
	 * @return mixed       Returns the data from the database
	 */
	public function get_stored_data( $key ) {
		return get_option( $key, false );
	}

	/**
	 * Give a key, store the value
	 *
	 * @since  1.5
	 *
	 * @param  string $key The option_name
	 * @param  mixed $value The value to store
	 *
	 * @return void
	 */
	public function store_data( $key, $value ) {
		return update_option( $key, $value );
	}

	/**
	 * Delete an option
	 *
	 * @since  1.5
	 *
	 * @param  string $key The option_name to delete
	 *
	 * @return void
	 */
	public function delete_data( $key ) {
		return delete_option( $key );
	}

}
