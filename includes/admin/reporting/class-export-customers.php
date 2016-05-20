<?php
/**
 * Donors Export Class
 *
 * This class handles donor export
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donors_Export Class
 *
 * @since 1.0
 */
class Give_Donors_Export extends Give_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'donors';

	/**
	 * Set the export headers
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}

		$extra = '';

		if ( ! empty( $_POST['give_export_download'] ) ) {
			$extra = sanitize_title( get_the_title( absint( $_POST['give_export_download'] ) ) ) . '-';
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'give_donors_export_filename', 'give-export-' . $extra . $this->export_type . '-' . date( 'm-d-Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		if ( ! empty( $_POST['give_export_download'] ) ) {
			$cols = array(
				'first_name' => __( 'First Name', 'give' ),
				'last_name'  => __( 'Last Name', 'give' ),
				'email'      => __( 'Email', 'give' ),
				'date'       => __( 'Date Donated', 'give' )
			);
		} else {

			$cols = array();

			if ( 'emails' != $_POST['give_export_option'] ) {
				$cols['name'] = __( 'Name', 'give' );
			}

			$cols['email'] = __( 'Email', 'give' );

			if ( 'full' == $_POST['give_export_option'] ) {
				$cols['purchases'] = __( 'Total Donations', 'give' );
				$cols['amount']    = __( 'Total Donated', 'give' ) . ' (' . html_entity_decode( give_currency_filter( '' ) ) . ')';
			}

		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  1.0
	 * @global object $wpdb      Used to query the database using the WordPress Database API
	 * @global object $give_logs Give Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		if ( ! empty( $_POST['give_export_download'] ) ) {

			// Export donors of a specific product
			global $give_logs;

			$args = array(
				'post_parent' => absint( $_POST['give_export_download'] ),
				'log_type'    => 'sale',
				'nopaging'    => true
			);

			if ( isset( $_POST['give_price_option'] ) ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_give_log_price_id',
						'value' => (int) $_POST['give_price_option']
					)
				);
			}

			$logs = $give_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {
					$payment_id = get_post_meta( $log->ID, '_give_log_payment_id', true );
					$user_info  = give_get_payment_meta_user_info( $payment_id );
					$data[]     = array(
						'first_name' => $user_info['first_name'],
						'last_name'  => $user_info['last_name'],
						'email'      => $user_info['email'],
						'date'       => $log->post_date
					);
				}
			}

		} else {

			// Export all donors
			$donors = Give()->customers->get_customers( array( 'number' => - 1 ) );

			$i = 0;

			foreach ( $donors as $donor ) {

				if ( 'emails' != $_POST['give_export_option'] ) {
					$data[ $i ]['name'] = $donor->name;
				}

				$data[ $i ]['email'] = $donor->email;

				if ( 'full' == $_POST['give_export_option'] ) {

					$data[ $i ]['purchases'] = $donor->purchase_count;
					$data[ $i ]['amount']    = give_format_amount( $donor->purchase_value );

				}
				$i ++;
			}
		}

		$data = apply_filters( 'give_export_get_data', $data );
		$data = apply_filters( 'give_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}