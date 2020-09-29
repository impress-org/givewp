<?php
/**
 * Recount donation form income and donations
 *
 * This class handles batch processing of recounting earnings and stats
 *
 * @subpackage  Admin/Tools/Give_Tools_Recount_Stats
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Tools_Recount_Form_Stats Class
 *
 * @since 1.5
 */
class Give_Tools_Recount_Form_Stats extends Give_Batch_Export {

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
	public $per_step = 30;

	/**
	 * Sets the donation form ID to recalculate
	 *
	 * @since  1.5
	 * @var integer
	 */
	protected $form_id = 0;

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

		$accepted_statuses = apply_filters( 'give_recount_accepted_statuses', [ 'publish' ] );

		if ( $this->step == 1 ) {
			$this->delete_data( 'give_temp_recount_form_stats' );
		}

		$totals = $this->get_stored_data( 'give_temp_recount_form_stats' );

		if ( false === $totals ) {
			$totals = [
				'earnings' => (float) 0,
				'sales'    => 0,
			];
			$this->store_data( 'give_temp_recount_form_stats', $totals );
		}

		$args = apply_filters(
			'give_recount_form_stats_args',
			[
				'give_forms' => $this->form_id,
				'number'     => $this->per_step,
				'status'     => $accepted_statuses,
				'paged'      => $this->step,
				'fields'     => 'ids',
			]
		);

		$payments = new Give_Payments_Query( $args );
		$payments = $payments->get_payments();

		if ( $payments ) {
			foreach ( $payments as $payment ) {

				// Ensure acceptable status only.
				if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
					continue;
				}

				// Ensure only payments for this form are counted.
				if ( $payment->form_id != $this->form_id ) {
					continue;
				}

				$currency_code = give_get_payment_currency_code( $payment->ID );

				/**
				 * Filter the payment amount.
				 *
				 * @since 2.1
				 */
				$earning_amount = apply_filters(
					'give_donation_amount',
					give_format_amount( $payment->total, [ 'donation_id' => $payment->ID ] ),
					$payment->total,
					$payment->ID,
					[
						'type'     => 'stats',
						'currency' => false,
						'amount'   => false,
					]
				);

				$totals['sales'] ++;
				$totals['earnings'] += (float) give_maybe_sanitize_amount( $earning_amount, [ 'currency' => $currency_code ] );

			}

			$this->store_data( 'give_temp_recount_form_stats', $totals );

			return true;
		}

		give_update_meta( $this->form_id, '_give_form_sales', $totals['sales'] );
		give_update_meta( $this->form_id, '_give_form_earnings', give_sanitize_amount_for_db( $totals['earnings'] ) );

		return false;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {
		if ( $this->step == 1 ) {
			$this->delete_data( 'give_recount_total_' . $this->form_id );
		}

		$accepted_statuses = apply_filters( 'give_recount_accepted_statuses', [ 'publish' ] );
		$total             = $this->get_stored_data( 'give_recount_total_' . $this->form_id );

		if ( false === $total ) {
			$total = 0;
			$args  = apply_filters(
				'give_recount_form_stats_total_args',
				[
					'give_forms' => $this->form_id,
					'number'     => - 1,
					'status'     => $accepted_statuses,
					'fields'     => 'ids',
				]
			);

			$payments = new Give_Payments_Query( $args );
			$total    = count( $payments->get_payments() );
			$this->store_data( 'give_recount_total_' . $this->form_id, $total );

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
		$this->form_id = isset( $request['form_id'] ) ? sanitize_text_field( $request['form_id'] ) : false;
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
			$this->delete_data( 'give_recount_total_' . $this->form_id );
			$this->delete_data( 'give_temp_recount_form_stats' );
			$this->done    = true;
			$this->message = sprintf( esc_html__( 'Donation counts and revenue amount statistics successfully recounted for "%s".', 'give' ), get_the_title( $this->form_id ) );

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
			$this->delete_data( 'give_temp_recount_form_stats' );
			$this->delete_data( 'give_recount_total_' . $this->form_id );
		}
	}
}
