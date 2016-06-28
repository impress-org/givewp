<?php
/**
 * Batch Customers Export Class
 *
 * This class handles customer export
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
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
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 1.5
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'id'        => __( 'ID', 'give' ),
			'name'      => __( 'Name', 'give' ),
			'email'     => __( 'Email', 'give' ),
			'purchases' => __( 'Number of Donations', 'give' ),
			'amount'    => __( 'Donor Value', 'give' )
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.5
	 *   Database API
	 * @global object $give_logs Give Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		if ( ! empty( $this->form ) ) {

			// Export customers of a specific product
			global $give_logs;

			$args = array(
				'post_parent'    => absint( $this->form ),
				'log_type'       => 'sale',
				'posts_per_page' => 30,
				'paged'          => $this->step
			);

			if ( null !== $this->price_id ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_give_log_price_id',
						'value' => (int) $this->price_id
					)
				);
			}

			$logs = $give_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {

					$payment_id  = get_post_meta( $log->ID, '_give_log_payment_id', true );
					$customer_id = give_get_payment_customer_id( $payment_id );
					$customer    = new Give_Customer( $customer_id );

					$data[] = array(
						'id'        => $customer->id,
						'name'      => $customer->name,
						'email'     => $customer->email,
						'purchases' => $customer->purchase_count,
						'amount'    => give_format_amount( $customer->purchase_value ),
					);
				}
			}

		} else {

			// Export all customers
			$offset    = 30 * ( $this->step - 1 );
			$customers = Give()->customers->get_customers( array( 'number' => 30, 'offset' => $offset ) );

			$i = 0;

			foreach ( $customers as $customer ) {

				$data[ $i ]['id']        = $customer->id;
				$data[ $i ]['name']      = $customer->name;
				$data[ $i ]['email']     = $customer->email;
				$data[ $i ]['purchases'] = $customer->purchase_count;
				$data[ $i ]['amount']    = give_format_amount( $customer->purchase_value );

				$i ++;
			}
		}

		$data = apply_filters( 'give_export_get_data', $data );
		$data = apply_filters( 'give_export_get_data_' . $this->export_type, $data );

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
	 * Set the properties specific to the Customers export
	 *
	 * @since 1.5
	 *
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start    = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end      = isset( $request['end'] ) ? sanitize_text_field( $request['end'] ) : '';
		$this->form = isset( $request['form'] ) ? absint( $request['form'] ) : null;
		$this->price_id = ! empty( $request['give_price_option'] ) && 0 !== $request['give_price_option'] ? absint( $request['give_price_option'] ) : null;
	}
}
