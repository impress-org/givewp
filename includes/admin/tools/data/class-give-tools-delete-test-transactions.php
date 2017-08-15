<?php
/**
 * Delete Test Transactions
 *
 * This class handles batch processing of deleting test transactions
 *
 * @subpackage  Admin/Tools/Give_Tools_Delete_Test_Transactions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Tools_Delete_Test_Transactions Class
 *
 * @since 1.5
 */
class Give_Tools_Delete_Test_Transactions extends Give_Batch_Export {

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
		global $wpdb;

		$items = $this->get_stored_data( 'give_temp_delete_test_ids' );

		if ( ! is_array( $items ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $items, $offset, $this->per_step );
		$meta_table = __give_v20_bc_table_details( 'payment' );

		if ( $step_items ) {

			$step_ids = array(
				'other' => array(),
			);

			foreach ( $step_items as $item ) {

				$step_ids['other'][] = $item['id'];

			}

			$sql = array();

			foreach ( $step_ids as $type => $ids ) {

				if ( empty( $ids ) ) {
					continue;
				}

				$parent_query = '';

				switch ( $type ) {
					case 'other':

						$temp_ids = implode( ',', $ids );

						// Get all the test logs of the donations ids.
						$parent_query = "SELECT DISTINCT post_id as id FROM $wpdb->postmeta WHERE meta_key = '_give_log_payment_id' AND meta_value IN ( $temp_ids )";
						$parent_ids   = $wpdb->get_results( $parent_query, 'ARRAY_A' );

						// List of all test logs.
						if ( $parent_ids ) {
							foreach ( $parent_ids as $parent_id ) {
								// Adding all the test log in post ids that are going to get deleted.
								$ids[] = $parent_id['id'];
							}
						}
						$ids = implode( ',', $ids );

						$sql[] = "DELETE FROM $wpdb->posts WHERE id IN ($ids)";
						$sql[] = "DELETE FROM {$meta_table['name']} WHERE {$meta_table['column']['id']} IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->comments WHERE comment_post_ID IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)";
						break;
				}

			}

			if ( ! empty( $sql ) ) {
				foreach ( $sql as $query ) {
					$wpdb->query( $query );
				}
				do_action( 'give_delete_log_cache' );
			}

			return true;
		}

		return false;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$items = $this->get_stored_data( 'give_temp_delete_test_ids', false );
		$total = count( $items );

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 1.5
	 *
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
	}

	/**
	 * Process a step
	 *
	 * @since 1.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to delete test transactions.', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			update_option( 'give_earnings_total', give_get_total_earnings( true ) );
			Give_Cache::delete( Give_Cache::get_key( 'give_estimated_monthly_stats' ) );

			$this->delete_data( 'give_temp_delete_test_ids' );

			// Reset the sequential order numbers
			if ( give_get_option( 'enable_sequential' ) ) {
				delete_option( 'give_last_payment_number' );
			}

			$this->done    = true;
			$this->message = __( 'Test transactions successfully deleted.', 'give' );

			return false;
		}
	}

	/**
	 * Headers
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}
	}

	/**
	 * Perform the export
	 *
	 * @access public
	 * @since 1.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		give_die();
	}

	/**
	 * Pre Fetch
	 */
	public function pre_fetch() {

		if ( $this->step == 1 ) {
			$this->delete_data( 'give_temp_delete_test_ids' );
		}

		$items = get_option( 'give_temp_delete_test_ids', false );

		if ( false === $items ) {
			$items = array();

			$args = apply_filters( 'give_tools_reset_stats_total_args', array(
				'post_status' => 'any',
				'number'      => - 1,
				'meta_key'    => '_give_payment_mode',
				'meta_value'  => 'test'
			) );

			$posts    = new Give_Payments_Query( $args );
			$payments = $posts->get_payments();

			/* @var Give_Payment $payment */
			foreach ( $payments as $payment ) {
				$items[] = array(
					'id'   => (int) $payment->ID,
					'type' => 'give_payment',
				);
			}
			
			// Allow filtering of items to remove with an unassociative array for each item.
			// The array contains the unique ID of the item, and a 'type' for you to use in the execution of the get_data method.
			$items = apply_filters( 'give_delete_test_items', $items );

			$this->store_data( 'give_temp_delete_test_ids', $items );
		}

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
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		return empty( $value ) ? false : maybe_unserialize( $value );
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
	private function store_data( $key, $value ) {
		global $wpdb;

		$value = maybe_serialize( $value );

		$data = array(
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		);

		$formats = array(
			'%s',
			'%s',
			'%s',
		);

		$wpdb->replace( $wpdb->options, $data, $formats );
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
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
