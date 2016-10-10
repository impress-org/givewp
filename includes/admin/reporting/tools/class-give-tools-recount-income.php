<?php
/**
 * Recount income
 *
 * This class handles batch processing of recounting income
 *
 * @subpackage  Admin/Tools/Give_Tools_Recount_Income
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Tools_Recount_Income Class
 *
 * @since 1.5
 */
class Give_Tools_Recount_Income extends Give_Batch_Export {

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
	public $per_step = 100;

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.5
	 *
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		if ( $this->step == 1 ) {
			$this->delete_data( 'give_temp_recount_income' );
		}

		$total = get_option( 'give_temp_recount_income', false );

		if ( false === $total ) {
			$total = (float) 0;
			$this->store_data( 'give_temp_recount_income', $total );
		}

		$accepted_statuses = apply_filters( 'give_recount_accepted_statuses', array( 'publish' ) );

		$args = apply_filters( 'give_recount_income_args', array(
			'number' => $this->per_step,
			'page'   => $this->step,
			'status' => $accepted_statuses,
			'fields' => 'ids'
		) );

		$payments = give_get_payments( $args );

		if ( ! empty( $payments ) ) {

			foreach ( $payments as $payment ) {

				$total += give_get_payment_amount( $payment );

			}

			if ( $total < 0 ) {
				$totals = 0;
			}

			$total = round( $total, give_currency_decimal_filter() );

			$this->store_data( 'give_temp_recount_income', $total );

			return true;

		}

		update_option( 'give_income_total', $total );
		set_transient( 'give_income_total', $total, 86400 );

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$total = $this->get_stored_data( 'give_recount_income_total' );

		if ( false === $total ) {
			$args = apply_filters( 'give_recount_income_total_args', array() );

			$counts = give_count_payments( $args );
			$total  = absint( $counts->publish );
			$total  = apply_filters( 'give_recount_store_income_total', $total );

			$this->store_data( 'give_recount_income_total', $total );
		}

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
			wp_die( esc_html__( 'You do not have permission to recount stats.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			$this->delete_data( 'give_recount_income_total' );
			$this->delete_data( 'give_temp_recount_income' );
			$this->done    = true;
			$this->message = esc_html__( 'Income stats have been successfully recounted.', 'give' );

			return false;
		}
	}

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
