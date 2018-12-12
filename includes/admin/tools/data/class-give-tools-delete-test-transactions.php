<?php
/**
 * Delete Test Transactions
 *
 * This class handles batch processing of deleting test transactions
 *
 * @subpackage  Admin/Tools/Give_Tools_Delete_Test_Transactions
 * @copyright   Copyright (c) 2016, GiveWP
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
	 * Constructor.
	 */
	public function __construct( $_step = 1 ) {
		parent::__construct( $_step );

		$this->is_writable = true;
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
		$items = $this->get_stored_data( 'give_temp_delete_test_ids' );

		if ( ! is_array( $items ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $items, $offset, $this->per_step );
		$meta_table = __give_v20_bc_table_details( 'payment' );

		if ( $step_items ) {
			foreach ( $step_items as $item ) {
				// Delete the main payment.
				give_delete_donation( absint( $item['id'] ) );
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
			update_option( 'give_earnings_total', give_get_total_earnings( true ), false );
			Give_Cache::delete( Give_Cache::get_key( 'give_estimated_monthly_stats' ) );

			$this->delete_data( 'give_temp_delete_test_ids' );

			$this->done    = true;
			$this->message = __( 'Test transactions successfully deleted.', 'give' );

			return false;
		}
	}

	/**
	 * Headers
	 */
	public function headers() {
		give_ignore_user_abort();
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
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'   => '_give_payment_mode',
						'value' => 'test',
					),
					array(
						'key'   => '_give_payment_gateway',
						'value' => 'manual',
					),
				),
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

		if ( empty( $value ) ) {
			return false;
		}

		$maybe_json = json_decode( $value );
		if ( ! is_null( $maybe_json ) ) {
			$value = json_decode( $value, true );
		}

		return $value;
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

		$value = is_array( $value ) ? wp_json_encode( $value ) : esc_attr( $value );

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

	/**
	 * Unset the properties specific to the donors export.
	 *
	 * @since 2.3.0
	 *
	 * @param array $request
	 * @param Give_Batch_Export $export
	 */
	public function unset_properties( $request, $export ) {
		if ( $export->done ) {
			// Delete all the donation ids.
			$this->delete_data( 'give_temp_delete_test_ids' );
		}
	}

}
