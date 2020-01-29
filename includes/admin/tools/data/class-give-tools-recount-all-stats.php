<?php
/**
 * Recount all donation counts and income stats
 *
 * This class handles batch processing of recounting donations and income stat totals
 *
 * @subpackage Admin/Tools/Give_Tools_Recount_All_Stats
 * @copyright  Copyright (c) 2016, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Tools_Recount_All_Stats Class
 *
 * @since 1.5
 */
class Give_Tools_Recount_All_Stats extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @since 1.5
	 * @var   string
	 */
	public $export_type = '';

	/**
	 * Allows for a non-form batch processing to be run.
	 *
	 * @since 1.5
	 * @var   bool
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 *
	 * @since 1.5
	 * @var   int
	 */
	public $per_step = 30;

	/**
	 * Display message on completing recount process
	 *
	 * @since 1.8.9
	 * @var   string
	 */
	public $message = '';

	/**
	 * Sets donation form id for recalculation
	 *
	 * @since 1.8.9
	 * @var   int
	 */
	protected $form_id = 0;

	/**
	 * Is Recount process completed
	 *
	 * @since 1.8.9
	 * @var   bool
	 */
	public $done = false;

	/**
	 * Constructor.
	 */
	public function __construct( $_step = 1 ) {
		parent::__construct( $_step );

		$this->is_writable = true;
	}

	/**
	 * Get the recount all stats data
	 *
	 * @access public
	 * @since  1.5
	 *
	 * @return bool
	 */
	public function get_data() {

		$totals             = $this->get_stored_data( 'give_temp_recount_all_stats' );
		$payment_items      = $this->get_stored_data( 'give_temp_payment_items' );
		$processed_payments = $this->get_stored_data( 'give_temp_processed_payments' );
		$accepted_statuses  = apply_filters( 'give_recount_accepted_statuses', array( 'publish' ) );

		if ( false === $totals ) {
			$totals = array();
		}

		if ( false === $payment_items ) {
			$payment_items = array();
		}

		if ( false === $processed_payments ) {
			$processed_payments = array();
		}

		$all_forms = $this->get_stored_data( 'give_temp_form_ids' );

		$payments = $this->get_stored_data( 'give_temp_all_payments_data' );

		if ( empty( $payments ) ) {
			$args = apply_filters(
				'give_recount_form_stats_args',
				array(
					'give_forms' => $all_forms,
					'number'     => $this->per_step,
					'status'     => $accepted_statuses,
					'paged'      => $this->step,
					'output'     => 'give_payments',
				)
			);

			$payments_query = new Give_Payments_Query( $args );
			$payments       = $payments_query->get_payments();
		}

		if ( ! empty( $payments ) ) {

			// Loop through payments
			foreach ( $payments as $payment ) {

				$payment_id = ( ! empty( $payment['ID'] ) ? absint( $payment['ID'] ) : ( ! empty( $payment->ID ) ? absint( $payment->ID ) : false ) );
				$payment    = new Give_Payment( $payment_id );

				// Prevent payments that have all ready been retrieved from a previous sales log from counting again.
				if ( in_array( $payment->ID, $processed_payments ) ) {
					continue;
				}

				// Verify accepted status.
				if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
					$processed_payments[] = $payment->ID;
					continue;
				}

				$payment_item = $payment_items[ $payment->ID ];

				$form_id = isset( $payment_item['id'] ) ? $payment_item['id'] : '';

				// Must have a form ID.
				if ( empty( $form_id ) ) {
					continue;
				}

				// Form ID must be within $all_forms array to be validated.
				if ( ! in_array( $form_id, $all_forms ) ) {
					continue;
				}

				// Set Sales count
				$totals[ $form_id ]['sales'] = isset( $totals[ $form_id ]['sales'] ) ?
					++ $totals[ $form_id ]['sales'] :
					1;

				// Set Total Earnings
				$totals[ $form_id ]['earnings'] = isset( $totals[ $form_id ]['earnings'] ) ?
					( $totals[ $form_id ]['earnings'] + $payment_item['price'] ) :
					$payment_item['price'];

				$processed_payments[] = $payment->ID;
			}

			// Get the list of form ids which does not contain any payment record.
			$remaining_form_ids = array_diff( $all_forms, array_keys( $totals ) );
			foreach ( $remaining_form_ids as $form_id ) {
				// If array key doesn't exist, create it
				if ( ! array_key_exists( $form_id, $totals ) ) {
					$totals[ $form_id ] = array(
						'sales'    => (int) 0,
						'earnings' => (float) 0,
					);
				}
			}

			$this->store_data( 'give_temp_processed_payments', $processed_payments );
			$this->store_data( 'give_temp_recount_all_stats', $totals );

			return true;
		}

		foreach ( $totals as $key => $stats ) {
			give_update_meta( $key, '_give_form_sales', $stats['sales'] );
			give_update_meta( $key, '_give_form_earnings', give_sanitize_amount_for_db( $stats['earnings'] ) );
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

		$total = $this->get_stored_data( 'give_recount_all_total' );

		if ( false === $total ) {
			$this->pre_fetch();
			$total = $this->get_stored_data( 'give_recount_all_total' );
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
			wp_die( esc_html__( 'You do not have permission to recount stats.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			$this->delete_data( 'give_recount_total_' . $this->form_id );
			$this->delete_data( 'give_recount_all_total' );
			$this->delete_data( 'give_temp_recount_all_stats' );
			$this->delete_data( 'give_temp_payment_items' );
			$this->delete_data( 'give_temp_form_ids' );
			$this->delete_data( 'give_temp_processed_payments' );
			$this->done    = true;
			$this->message = esc_html__( 'Donation form income amounts and donation counts stats successfully recounted.', 'give' );

			return false;
		}
	}

	/**
	 * Set headers.
	 */
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
	 * Pre Fetch Data
	 *
	 * @access public
	 * @since  1.5
	 */
	public function pre_fetch() {

		if ( 1 == $this->step ) {
			$this->delete_data( 'give_temp_recount_all_total' );
			$this->delete_data( 'give_temp_recount_all_stats' );
			$this->delete_data( 'give_temp_payment_items' );
			$this->delete_data( 'give_temp_processed_payments' );
			$this->delete_data( 'give_temp_all_payments_data' );
		}

		$accepted_statuses = apply_filters( 'give_recount_accepted_statuses', array( 'publish' ) );
		$total             = $this->get_stored_data( 'give_temp_recount_all_total' );

		if ( false === $total ) {

			$payment_items = $this->get_stored_data( 'give_temp_payment_items' );

			if ( false === $payment_items ) {
				$payment_items = array();
				$this->store_data( 'give_temp_payment_items', $payment_items );
			}

			$args = array(
				'post_status'    => 'publish',
				'post_type'      => 'give_forms',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			);

			$all_forms = get_posts( $args );

			$this->store_data( 'give_temp_form_ids', $all_forms );

			$args = apply_filters(
				'give_recount_form_stats_total_args',
				array(
					'give_forms' => $all_forms,
					'number'     => $this->per_step,
					'status'     => $accepted_statuses,
					'page'       => $this->step,
					'output'     => 'payments',
				)
			);

			$payments_query = new Give_Payments_Query( $args );
			$payments       = $payments_query->get_payments();

			$total = wp_count_posts( 'give_payment' )->publish;

			$this->store_data( 'give_temp_all_payments_data', $payments );

			if ( $payments ) {

				foreach ( $payments as $payment ) {

					$form_id = $payment->form_id;

					// If for some reason somehow the form_ID isn't set check payment meta
					if ( empty( $payment->form_id ) ) {
						$payment_meta = $payment->get_meta();
						$form_id      = isset( $payment_meta['form_id'] ) ? $payment_meta['form_id'] : 0;
					}

					if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
						continue;
					}

					$currency_code = give_get_payment_currency_code( $payment->ID );

					if ( ! array_key_exists( $payment->ID, $payment_items ) ) {

						/**
						 * Filter the payment amount.
						 *
						 * @since 2.1
						 */
						$payment_total = apply_filters(
							'give_donation_amount',
							give_format_amount( $payment->total, array( 'donation_id' => $payment->ID ) ),
							$payment->total,
							$payment->ID,
							array(
								'type'     => 'stats',
								'currency' => false,
								'amount'   => false,
							)
						);

						$payment_items[ $payment->ID ] = array(
							'id'         => $form_id,
							'payment_id' => $payment->ID,
							'price'      => (float) give_maybe_sanitize_amount( $payment_total, array( 'currency' => $currency_code ) ),
						);
					}
				}
			}
			$this->store_data( 'give_temp_payment_items', $payment_items );
			$this->store_data( 'give_recount_all_total', $total );
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
			$this->delete_data( 'give_temp_all_payments_data' );
			$this->delete_data( 'give_recount_total_' . $this->form_id );
			$this->delete_data( 'give_recount_all_total' );
			$this->delete_data( 'give_temp_recount_all_stats' );
			$this->delete_data( 'give_temp_payment_items' );
			$this->delete_data( 'give_temp_form_ids' );
			$this->delete_data( 'give_temp_processed_payments' );
		}
	}
}
