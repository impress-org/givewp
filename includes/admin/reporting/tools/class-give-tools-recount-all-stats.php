<?php
/**
 * Recount all donation counts and income stats
 *
 * This class handles batch processing of recounting donations and income stat totals
 *
 * @subpackage  Admin/Tools/Give_Tools_Recount_All_Stats
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
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
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $give_logs, $wpdb;

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

		$args = apply_filters( 'give_recount_form_stats_args', array(
			'post_parent__in' => $all_forms,
			'post_type'       => 'give_log',
			'posts_per_page'  => $this->per_step,
			'post_status'     => 'publish',
			'paged'           => $this->step,
			'log_type'        => 'sale',
			'fields'          => 'ids',
		) );

		$log_ids = $give_logs->get_connected_logs( $args, 'sale' );

		if ( $log_ids ) {
			$log_ids = implode( ',', $log_ids );

			$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_give_log_payment_id' AND post_id IN ($log_ids)" );
			unset( $log_ids );

			$payment_ids = implode( ',', $payment_ids );
			$payments    = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $payment_ids . ")" );
			unset( $payment_ids );

			//Loop through payments
			foreach ( $payments as $payment ) {

				// Prevent payments that have all ready been retrieved from a previous sales log from counting again.
				if ( in_array( $payment->ID, $processed_payments ) ) {
					continue;
				}

				//Verify accepted status'
				if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
					$processed_payments[] = $payment->ID;
					continue;
				}

				$payment_item = $payment_items[ $payment->ID ];


				$form_id = isset( $payment_item['id'] ) ? $payment_item['id'] : '';

				//Must have a form ID
				if ( empty( $form_id ) ) {
					continue;
				}

				//Form ID must be within $all_forms array to be validated
				if ( ! in_array( $form_id, $all_forms ) ) {
					continue;
				}

				//If array key doesn't exist, create it
				if ( ! array_key_exists( $form_id, $totals ) ) {
					$totals[ $form_id ] = array(
						'sales'    => (int) 0,
						'earnings' => (float) 0,
					);
				}

				$totals[ $form_id ]['sales'] ++;
				$totals[ $form_id ]['earnings'] += $payment_item['price'];

				$processed_payments[] = $payment->ID;

			}

			$this->store_data( 'give_temp_processed_payments', $processed_payments );
			$this->store_data( 'give_temp_recount_all_stats', $totals );

			return true;
		}

		foreach ( $totals as $key => $stats ) {
			update_post_meta( $key, '_give_form_sales', $stats['sales'] );
			update_post_meta( $key, '_give_form_earnings', $stats['earnings'] );
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

		$total = $this->get_stored_data( 'give_recount_all_total', false );

		if ( false === $total ) {
			$this->pre_fetch();
			$total = $this->get_stored_data( 'give_recount_all_total', 0 );
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
	 * Pre Fetch Data
	 *
	 * @access public
	 * @since 1.5
	 */
	public function pre_fetch() {

		global $give_logs, $wpdb;

		if ( $this->step == 1 ) {
			$this->delete_data( 'give_temp_recount_all_total' );
			$this->delete_data( 'give_temp_recount_all_stats' );
			$this->delete_data( 'give_temp_payment_items' );
			$this->delete_data( 'give_temp_processed_payments' );
		}

		$accepted_statuses = apply_filters( 'give_recount_accepted_statuses', array( 'publish' ) );
		$total             = $this->get_stored_data( 'give_temp_recount_all_total' );

		if ( false === $total ) {
			$total         = 0;
			$payment_items = $this->get_stored_data( 'give_temp_payment_items' );

			if ( false === $payment_items ) {
				$payment_items = array();
				$this->store_data( 'give_temp_payment_items', $payment_items );
			}

			$all_forms = $this->get_stored_data( 'give_temp_form_ids' );

			if ( false === $all_forms ) {
				$args = array(
					'post_status'    => 'any',
					'post_type'      => 'give_forms',
					'posts_per_page' => - 1,
					'fields'         => 'ids',
				);

				$all_forms = get_posts( $args );
				$this->store_data( 'give_temp_form_ids', $all_forms );
			}

			$args = apply_filters( 'give_recount_form_stats_total_args', array(
				'post_parent__in' => $all_forms,
				'post_type'       => 'give_log',
				'post_status'     => 'publish',
				'log_type'        => 'sale',
				'fields'          => 'ids',
				'nopaging'        => true,
			) );

			$all_logs = $give_logs->get_connected_logs( $args, 'sale' );

			if ( $all_logs ) {
				$log_ids     = implode( ',', $all_logs );
				$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_give_log_payment_id' AND post_id IN ($log_ids)" );
				unset( $log_ids );

				$payment_ids = implode( ',', $payment_ids );
				$payments    = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $payment_ids . ")" );
				unset( $payment_ids );

				foreach ( $payments as $payment ) {

					$payment = new Give_Payment( $payment->ID );
					$form_id = $payment->form_id;

					//If for some reason somehow the form_ID isn't set check payment meta
					if ( empty( $payment->form_id ) ) {
						$payment_meta = $payment->get_meta();
						$form_id = isset( $payment_meta['form_id'] ) ? $payment_meta['form_id'] : 0;
					}

					if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
						continue;
					}

					if ( ! array_key_exists( $payment->ID, $payment_items ) ) {
						$payment_items[ $payment->ID ] = array(
							'id'         => $form_id,
							'payment_id' => $payment->ID,
							'price'      => $payment->total
						);
					}

				}

				$total = count( $all_logs );
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
