<?php
/**
 * Recount earnings
 *
 * This class handles batch processing of recounting earnings
 *
 * @subpackage  Admin/Tools/Give_Tools_Recount_Income
 * @copyright   Copyright (c) 2016, GiveWP
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
	 *
	 * @var string
	 * @since 1.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-form batch processing to be run.
	 *
	 * @since  1.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 *
	 * @since  1.5
	 * @var integer
	 */
	public $per_step = 100;

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
	 *
	 * @return bool
	 */
	public function get_data() {

		if ( $this->step == 1 ) {
			$this->delete_data( 'give_temp_recount_earnings' );
		}

		$total = get_option( 'give_temp_recount_earnings', false );

		if ( false === $total ) {
			$total = (float) 0;
			$this->store_data( 'give_temp_recount_earnings', $total );
		}

		$accepted_statuses = apply_filters( 'give_recount_accepted_statuses', [ 'publish' ] );

		$args = apply_filters(
			'give_recount_earnings_args',
			[
				'number' => $this->per_step,
				'page'   => $this->step,
				'status' => $accepted_statuses,
			]
		);

		$payments = give_get_payments( $args );

		if ( ! empty( $payments ) ) {

			foreach ( $payments as $payment ) {
				// Get the payment amount.
				$payment_amount = give_get_meta( $payment->ID, '_give_payment_total', true );

				/**
				 * Filter the payment amount.
				 *
				 * @since 2.1
				 */
				$donation_amount = apply_filters(
					'give_donation_amount',
					give_format_amount( $payment_amount, [ 'donation_id' => $payment->ID ] ),
					$payment->total,
					$payment->ID,
					[
						'type'     => 'stats',
						'currency' => false,
						'amount'   => false,
					]
				);

				$total += (float) give_maybe_sanitize_amount( $donation_amount );
			}

			if ( $total < 0 ) {
				$totals = 0;
			}

			$total = round( $total, give_get_price_decimals() );

			$this->store_data( 'give_temp_recount_earnings', $total );

			return true;

		}

		update_option( 'give_earnings_total', $total, false );

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$total = $this->get_stored_data( 'give_recount_earnings_total' );

		if ( false === $total ) {
			$args = apply_filters( 'give_recount_earnings_total_args', [] );

			$counts = give_count_payments( $args );
			$total  = absint( $counts->publish );
			$total  = apply_filters( 'give_recount_store_earnings_total', $total );

			$this->store_data( 'give_recount_earnings_total', $total );
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
			wp_die( esc_html__( 'You do not have permission to recount stats.', 'give' ), esc_html__( 'Error', 'give' ), [ 'response' => 403 ] );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			$this->delete_data( 'give_recount_earnings_total' );
			$this->delete_data( 'give_temp_recount_earnings' );
			$this->done    = true;
			$this->message = esc_html__( 'Revenue stats have been successfully recounted.', 'give' );

			return false;
		}
	}

	/**
	 * Headers.
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
	 * @param  mixed  $value The value to store
	 *
	 * @return void
	 */
	private function store_data( $key, $value ) {
		global $wpdb;

		$value = is_array( $value ) ? wp_json_encode( $value ) : esc_attr( $value );

		$data = [
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		];

		$formats = [
			'%s',
			'%s',
			'%s',
		];

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
		$wpdb->delete( $wpdb->options, [ 'option_name' => $key ] );
	}

	/**
	 * Unset the properties specific to the donors export.
	 *
	 * @since 2.3.0
	 *
	 * @param array             $request
	 * @param Give_Batch_Export $export
	 */
	public function unset_properties( $request, $export ) {
		if ( $export->done ) {
			// Delete all the donation ids.
			$this->delete_data( 'give_temp_recount_earnings' );
			$this->delete_data( 'give_recount_earnings_total' );
		}
	}

}
