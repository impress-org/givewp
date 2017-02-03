<?php
/**
 * Batch Customers Export Class
 *
 * This class handles customer export
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Batch_Customers_Export Class
 *
 * @since 1.5
 */
class Give_Batch_Customers_Export extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.5
	 */
	public $export_type = 'customers';

	/**
	 * Form submission data
	 *
	 * @var array
	 * @since 1.5
	 */
	private $data = array();

	/**
	 * Array of donor ids which is already included in csv file.
	 *
	 * @since 1.8
	 * @var array
	 */
	private $donor_ids = array();

	/**
	 * Export query id.
	 *
	 * @since 1.8
	 * @var array
	 */
	private $query_id = '';

	/**
	 * Set the properties specific to the Customers export
	 *
	 * @since 1.5
	 *
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {

		// Set data from form submission
		if ( isset( $_POST['form'] ) ) {
			parse_str( $_POST['form'], $this->data );
		}

		$this->form = $this->data['forms'];

		// Setup donor ids cache.
		if( ! empty( $this->form ) ) {
			// Cache donor ids to output unique list of donor.
			$this->query_id = give_clean( $_REQUEST['give_export_option']['query_id'] );
			if( ! ( $this->donor_ids = get_transient( $this->query_id ) ) ) {
				$this->donor_ids = array();
				set_transient( $this->query_id, $this->donor_ids, HOUR_IN_SECONDS );
			}
		}

		$this->price_id = ! empty( $request['give_price_option'] ) && 0 !== $request['give_price_option'] ? absint( $request['give_price_option'] ) : null;

	}

	/**
	 * Set the CSV columns.
	 *
	 * @access public
	 * @since  1.5
	 * @return array|bool $cols All the columns.
	 */
	public function csv_cols() {

		$cols = array();

		$columns = isset( $this->data['give_export_option'] ) ? $this->data['give_export_option'] : array();

		// We need columns.
		if ( empty( $columns ) ) {
			return false;
		}

		$cols = $this->get_cols( $columns );

		return $cols;
	}

	/**
	 * CSV file columns.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	private function get_cols( $columns ) {

		$cols = array();

		foreach ( $columns as $key => $value ) {

			switch ( $key ) {
				case 'full_name' :
					$cols['full_name'] = esc_html__( 'Full Name', 'give' );
					break;
				case 'email' :
					$cols['email'] = esc_html__( 'Email Address', 'give' );
					break;
				case 'address' :
					$cols['address_line1']   = esc_html__( 'Address', 'give' );
					$cols['address_line2']   = esc_html__( 'Address 2', 'give' );
					$cols['address_city']    = esc_html__( 'City', 'give' );
					$cols['address_state']   = esc_html__( 'State', 'give' );
					$cols['address_zip']     = esc_html__( 'Zip', 'give' );
					$cols['address_country'] = esc_html__( 'Country', 'give' );
					break;
				case 'userid' :
					$cols['userid'] = esc_html__( 'User ID', 'give' );
					break;
				case 'date_first_donated' :
					$cols['date_first_donated'] = esc_html__( 'First Donation Date', 'give' );
					break;
				case 'donations' :
					$cols['donations'] = esc_html__( 'Number of Donations', 'give' );
					break;
				case 'donation_sum' :
					$cols['donation_sum'] = esc_html__( 'Sum of Donations', 'give' );
					break;
			}
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

		if ( ! empty( $this->form ) ) {

			// Export donors of a specific product
			global $give_logs;

			$args = array(
				'post_parent'    => absint( $this->form ),
				'log_type'       => 'sale',
				'posts_per_page' => 30,
				'paged'          => $this->step,
			);

			// Check for price option
			if ( null !== $this->price_id ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_give_log_price_id',
						'value' => (int) $this->price_id,
					),
				);
			}

			$logs = $give_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {
					$payment_id = get_post_meta( $log->ID, '_give_log_payment_id', true );
					$payment    = new Give_Payment( $payment_id );

					// Continue if donor already included.
					if( in_array( $payment->customer_id , $this->donor_ids ) ) {
						continue;
					}

					$this->donor_ids[] = $payment->customer_id;

					$donor      = Give()->customers->get_customer_by( 'id', $payment->customer_id );
					$data[]     = $this->set_donor_data( $i, $data, $donor );
					$i ++;
				}

				// Cache donor ids only if admin export donor for specific form.
				set_transient( $this->query_id, array_unique( $this->donor_ids ), HOUR_IN_SECONDS );
			}
		} else {

			// Export all customers
			$offset = 30 * ( $this->step - 1 );
			$donors = Give()->customers->get_customers( array( 'number' => 30, 'offset' => $offset ) );

			foreach ( $donors as $donor ) {

				$data[] = $this->set_donor_data( $i, $data, $donor );
				$i ++;
			}
		}


		$data = apply_filters( 'give_export_get_data', $data );
		$data = apply_filters( "give_export_get_data_{$this->export_type}", $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$percentage = 0;

		// We can't count the number when getting them for a specific form
		if ( empty( $this->form ) ) {

			$total = Give()->customers->count();

			if ( $total > 0 ) {

				$percentage = ( ( 30 * $this->step ) / $total ) * 100;

			}
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set Donor Data
	 *
	 * @param int           $i
	 * @param array         $data
	 * @param Give_Customer $donor
	 *
	 * @return mixed
	 */
	private function set_donor_data( $i, $data, $donor ) {

		$columns = $this->csv_cols();

		// Set address variable
		$address = '';
		if ( isset( $donor->user_id ) && $donor->user_id > 0 ) {
			$address = give_get_donor_address( $donor->user_id );
		}

		// Set columns
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
			$data[ $i ]['date_first_donated'] = date_i18n( give_date_format(), strtotime( $donor->date_created ) );
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
