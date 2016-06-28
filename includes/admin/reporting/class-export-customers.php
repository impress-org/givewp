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

		if ( ! empty( $_POST['give_export_form'] ) ) {
			$extra = sanitize_title( get_the_title( absint( $_POST['give_export_form'] ) ) ) . '-';
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

		$cols = array();

		$columns = isset( $_POST['give_export_option'] ) ? $_POST['give_export_option'] : array();

		if ( empty( $columns ) ) {
			return false;
		}
		if ( ! empty( $columns['full_name'] ) ) {
			$cols['full_name'] = __( 'Full Name', 'give' );
		}
		if ( ! empty( $columns['email'] ) ) {
			$cols['email'] = __( 'Email Address', 'give' );
		}
		if ( ! empty( $columns['address'] ) ) {
			$cols['address_line1']   = __( 'Address Line 1', 'give' );
			$cols['address_line2']   = __( 'Address Line 2', 'give' );
			$cols['address_city']    = __( 'City', 'give' );
			$cols['address_state']   = __( 'State', 'give' );
			$cols['address_zip']     = __( 'Zip', 'give' );
			$cols['address_country'] = __( 'Country', 'give' );
		}
		if ( ! empty( $columns['userid'] ) ) {
			$cols['userid'] = __( 'User ID', 'give' );
		}
		if ( ! empty( $columns['date_first_donated'] ) ) {
			$cols['date_first_donated'] = __( 'First Donation Date', 'give' );
		}
		if ( ! empty( $columns['donations'] ) ) {
			$cols['donations'] = __( 'Number of Donations', 'give' );
		}
		if ( ! empty( $columns['donation_sum'] ) ) {
			$cols['donation_sum'] = __( 'Sum of Donations', 'give' );
		}

		return $cols;

	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  1.0
	 * @global object $give_logs Give Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		$i = 0;

		if ( ! empty( $_POST['give_export_form'] ) ) {

			// Export donors of a specific product
			global $give_logs;

			$args = array(
				'post_parent' => absint( $_POST['give_export_form'] ),
				'log_type'    => 'sale',
				'nopaging'    => true
			);

			//Check for price option
			if ( isset( $_POST['give_price_option'] ) ) {

				//Add meta query for this price id
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
					$payment    = new Give_Payment( $payment_id );
					$donor      = Give()->customers->get_customer_by( 'id', $payment->customer_id );
					$data[]     = $this->set_donor_data( $i, $data, $donor );
					$i ++;
				}
			}

		} else {

			// Export all donors
			$donors = Give()->customers->get_customers( array( 'number' => - 1 ) );

			foreach ( $donors as $donor ) {

				$data[] = $this->set_donor_data( $i, $data, $donor );
				$i ++;
			}
		}

		$data = apply_filters( 'give_export_get_data', $data );
		$data = apply_filters( 'give_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Set Donor Data
	 *
	 * @param $donor
	 */
	private function set_donor_data( $i, $data, $donor ) {

		$columns = $this->csv_cols();

		//Set address variable
		$address = '';
		if ( isset( $donor->user_id ) && $donor->user_id > 0 ) {
			$address = give_get_donor_address( $donor->user_id );
		}

		//Set columns
		if ( ! empty( $columns['full_name'] ) ) {
			$data[ $i ]['full_name'] = $donor->name;
		}
		if ( ! empty( $columns['email'] ) ) {
			$data[ $i ]['email'] = $donor->email;
		}
		if ( ! empty( $columns['address_line1'] ) ) {

			$data[ $i ]['address_line1']   = isset( $address['line1'] ) ? $address['line1'] : '';
			$data[ $i ]['address_line2']   = isset( $address['line2'] ) ? $address['line2'] : '';
			$data[ $i ]['address_city']    = isset( $address['city'] ) ? $address['city'] : '';
			$data[ $i ]['address_state']   = isset( $address['state'] ) ? $address['state'] : '';
			$data[ $i ]['address_zip']     = isset( $address['zip'] ) ? $address['zip'] : '';
			$data[ $i ]['address_country'] = isset( $address['country'] ) ? $address['country'] : '';
		}
		if ( ! empty( $columns['userid'] ) ) {
			$data[ $i ]['userid'] = ! empty( $donor->user_id ) ? $donor->user_id : '';
		}
		if ( ! empty( $columns['date_first_donated'] ) ) {
			$data[ $i ]['date_first_donated'] = date_i18n( get_option( 'date_format' ), strtotime( $donor->date_created ) );
		}
		if ( ! empty( $columns['donations'] ) ) {
			$data[ $i ]['donations'] = $donor->purchase_count;
		}
		if ( ! empty( $columns['donation_sum'] ) ) {
			$data[ $i ]['donation_sum'] = give_format_amount( $donor->purchase_value );
		}

		return $data[ $i ];

	}


}