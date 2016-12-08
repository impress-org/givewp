<?php
/**
 * Recount donation form income and donations
 *
 * This class handles batch processing of recounting earnings and stats
 *
 * @subpackage  Admin/Tools/Give_Tools_Recount_Stats
 * @copyright   Copyright (c) 2016, WordImpress
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
	 * Sets the donation form ID to recalculate
	 * @since  1.5
	 * @var integer
	 */
	protected $form_id = null;

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

		$accepted_statuses = apply_filters( 'give_recount_accepted_statuses', array( 'publish' ) );

		if ( $this->step == 1 ) {
			$this->delete_data( 'give_temp_recount_form_stats' );
		}

		$totals = $this->get_stored_data( 'give_temp_recount_form_stats' );

		if ( false === $totals ) {
			$totals = array(
				'earnings' => (float) 0,
				'sales'    => 0,
			);
			$this->store_data( 'give_temp_recount_form_stats', $totals );
		}

		$args = apply_filters( 'give_recount_form_stats_args', array(
			'post_parent'    => $this->form_id,
			'post_type'      => 'give_log',
			'posts_per_page' => $this->per_step,
			'post_status'    => 'publish',
			'paged'          => $this->step,
			'log_type'       => 'sale',
			'fields'         => 'ids',
		) );

		$log_ids              = $give_logs->get_connected_logs( $args, 'sale' );
		$this->_log_ids_debug = array();

		if ( $log_ids ) {
			$log_ids     = implode( ',', $log_ids );
			$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_give_log_payment_id' AND post_id IN ($log_ids)" );
			unset( $log_ids );

			$payment_ids = implode( ',', $payment_ids );
			$payments    = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $payment_ids . ")" );
			unset( $payment_ids );

			foreach ( $payments as $payment ) {

				$payment = new Give_Payment( $payment->ID );

				//Ensure acceptible status only
				if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
					continue;
				}

				//Ensure only payments for this form are counted
				if ( $payment->form_id != $this->form_id ) {
					continue;
				}

				$this->_log_ids_debug[] = $payment->ID;

				$totals['sales'] ++;
				$totals['earnings'] += $payment->total;

			}

			$this->store_data( 'give_temp_recount_form_stats', $totals );

			return true;
		}


		update_post_meta( $this->form_id, '_give_form_sales', $totals['sales'] );
		update_post_meta( $this->form_id, '_give_form_earnings', $totals['earnings'] );

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {
		global $give_logs, $wpdb;

		if ( $this->step == 1 ) {
			$this->delete_data( 'give_recount_total_' . $this->form_id );
		}

		$accepted_statuses = apply_filters( 'give_recount_accepted_statuses', array( 'publish' ) );
		$total             = $this->get_stored_data( 'give_recount_total_' . $this->form_id );

		if ( false === $total ) {
			$total = 0;
			$args  = apply_filters( 'give_recount_form_stats_total_args', array(
				'post_parent' => $this->form_id,
				'post_type'   => 'give_log',
				'post_status' => 'publish',
				'log_type'    => 'sale',
				'fields'      => 'ids',
				'nopaging'    => true,
			) );

			$log_ids = $give_logs->get_connected_logs( $args, 'sale' );

			if ( $log_ids ) {
				$log_ids     = implode( ',', $log_ids );
				$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_give_log_payment_id' AND post_id IN ($log_ids)" );
				unset( $log_ids );

				$payment_ids = implode( ',', $payment_ids );
				$payments    = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $payment_ids . ")" );
				unset( $payment_ids );

				foreach ( $payments as $payment ) {
					if ( in_array( $payment->post_status, $accepted_statuses ) ) {
						continue;
					}

					$total ++;
				}
			}

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
			wp_die( esc_html__( 'You do not have permission to recount stats.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			$this->delete_data( 'give_recount_total_' . $this->form_id );
			$this->delete_data( 'give_temp_recount_form_stats' );
			$this->done    = true;
			$this->message = sprintf( esc_html__( 'Donation counts and income amount statistics successfully recounted for "%s".', 'give' ), get_the_title( $this->form_id ) );

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
