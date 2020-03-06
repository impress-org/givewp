<?php
/**
 * Recount single donor stats.
 *
 * This class handles batch processing of recounting a single donor's stats.
 *
 * @subpackage  Admin/Tools/Give_Tools_Recount_Single_Customer_Stats
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Tools_Recount_Single_Customer_Stats Class
 *
 * @since 1.5
 */
class Give_Tools_Recount_Single_Customer_Stats extends Give_Batch_Export {

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
	public $per_step = 10;

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
	 * @since  1.5
	 * @global object $wpdb Used to query the database using the WordPress Database API
	 * @return bool
	 */
	public function get_data() {

		$donor    = new Give_Donor( $this->customer_id );
		$payments = $this->get_stored_data( 'give_recount_donor_payments_' . $donor->id );

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $payments, $offset, $this->per_step );

		if ( count( $step_items ) > 0 ) {
			$pending_total = (float) $this->get_stored_data( 'give_stats_donor_pending_total' . $donor->id );
			$step_total    = 0;

			$found_payment_ids = $this->get_stored_data( 'give_stats_found_payments_' . $donor->id );

			foreach ( $step_items as $payment ) {
				$payment = get_post( $payment->ID );

				if ( is_null( $payment ) || is_wp_error( $payment ) || 'give_payment' !== $payment->post_type ) {

					$missing_payments   = $this->get_stored_data( 'give_stats_missing_payments' . $donor->id );
					$missing_payments[] = $payment->ID;
					$this->store_data( 'give_stats_missing_payments' . $donor->id, $missing_payments );

					continue;
				}

				$should_process_payment = 'publish' == $payment->post_status ? true : false;
				$should_process_payment = apply_filters( 'give_donor_recount_should_process_donation', $should_process_payment, $payment );

				if ( true === $should_process_payment ) {

					$found_payment_ids[] = $payment->ID;

					if ( apply_filters( 'give_donor_recount_should_increase_value', true, $payment ) ) {
						$payment_amount = (float) give_donation_amount( $payment->ID, array( 'type' => 'stats' ) );
						$step_total    += $payment_amount;
					}
				}
			}

			$updated_total = $pending_total + $step_total;
			$this->store_data( 'give_stats_donor_pending_total' . $donor->id, $updated_total );
			$this->store_data( 'give_stats_found_payments_' . $donor->id, $found_payment_ids );

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

		$payments = $this->get_stored_data( 'give_recount_donor_payments_' . $this->customer_id );
		$total    = count( $payments );

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
		$this->customer_id = isset( $request['customer_id'] ) ? sanitize_text_field( $request['customer_id'] ) : false;
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
			$donor       = new Give_Donor( $this->customer_id );
			$payment_ids = get_option( 'give_stats_found_payments_' . $donor->id, array() );
			$this->delete_data( 'give_stats_found_payments_' . $donor->id );

			$removed_payments = array_unique( get_option( 'give_stats_missing_payments' . $donor->id, array() ) );

			// Find non-existing payments (deleted) and total up the donation count
			$purchase_count = 0;
			foreach ( $payment_ids as $key => $payment_id ) {
				if ( in_array( $payment_id, $removed_payments ) ) {
					unset( $payment_ids[ $key ] );
					continue;
				}

				$payment = get_post( $payment_id );
				if ( apply_filters( 'give_donor_recount_should_increase_count', true, $payment ) ) {
					$purchase_count ++;
				}
			}

			$this->delete_data( 'give_stats_missing_payments' . $donor->id );

			$pending_total = $this->get_stored_data( 'give_stats_donor_pending_total' . $donor->id );
			$this->delete_data( 'give_stats_donor_pending_total' . $donor->id );
			$this->delete_data( 'give_recount_donor_stats_' . $donor->id );
			$this->delete_data( 'give_recount_donor_payments_' . $this->customer_id );

			$payment_ids = implode( ',', $payment_ids );
			$donor->update(
				array(
					'payment_ids'    => $payment_ids,
					'purchase_count' => $purchase_count,
					'purchase_value' => $pending_total,
				)
			);

			$this->done    = true;
			$this->message = esc_html__( 'Donor stats have been successfully recounted.', 'give' );

			return false;
		}
	}

	public function headers() {
		give_ignore_user_abort();
	}

	/**
	 * Perform the export
	 *
	 * @access public
	 * @since  1.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		give_die();
	}

	/**
	 * Zero out the data on step one
	 *
	 * @access public
	 * @since  1.5
	 * @return void
	 */
	public function pre_fetch() {
		if ( $this->step === 1 ) {
			$allowed_payment_status = apply_filters( 'give_recount_donors_donation_statuses', give_get_payment_status_keys() );

			// Before we start, let's zero out the customer's data
			$donor = new Give_Donor( $this->customer_id );
			$donor->update(
				array(
					'purchase_value' => give_format_amount( 0, array( 'sanitize' => false ) ),
					'purchase_count' => 0,
				)
			);

			$attached_payment_ids = explode( ',', $donor->payment_ids );

			$attached_args = array(
				'post__in' => $attached_payment_ids,
				'number'   => - 1,
				'status'   => $allowed_payment_status,
			);

			$attached_payments = give_get_payments( $attached_args );

			$unattached_args = array(
				'post__not_in' => $attached_payment_ids,
				'number'       => - 1,
				'status'       => $allowed_payment_status,
				'meta_query'   => array(
					array(
						'key'   => '_give_payment_donor_email',
						'value' => $donor->email,
					),
				),
			);

			$unattached_payments = give_get_payments( $unattached_args );

			$payments = array_merge( $attached_payments, $unattached_payments );

			$this->store_data( 'give_recount_donor_payments_' . $donor->id, $payments );
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
	 * @param  string $key   The option_name
	 * @param  mixed  $value The value to store
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
}
