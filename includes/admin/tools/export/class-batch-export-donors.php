<?php
/**
 * Batch Donors Export Class
 *
 * This class handles donor export.
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
 * Give_Batch_Donors_Export Class
 *
 * @since 1.5
 */
class Give_Batch_Donors_Export extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var string
	 * @since 1.5
	 */
	public $export_type = 'donors';

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
	 * Array of payment stats which is already included in csv file.
	 *
	 * @since 1.8.9
	 * @var array
	 */
	private $payment_stats = array();

	/**
	 * Export query id.
	 *
	 * @since 1.8
	 * @var string
	 */
	private $query_id = '';

	/**
	 * Give_Batch_Export constructor.
	 *
	 * @since 2.1.0
	 *
	 * @param int $_step
	 */
	public function __construct( $_step = 1 ) {

		parent::__construct( $_step );

		// Filter to change the filename.
		add_filter( 'give_export_filename', array( $this, 'give_export_filename' ), 10, 2 );
	}

	/**
	 * Function to change the filename
	 *
	 * @since 2.1.0
	 *
	 * @param string $filename File name.
	 * @param string $export_type export type.
	 *
	 * @return string $filename file name.
	 */
	public function give_export_filename( $filename, $export_type ) {

		if ( $this->export_type !== $export_type ) {
			return $filename;
		}

		$forms = empty( $_GET['forms'] ) ? 0 : absint( $_GET['forms'] );

		if ( $forms ) {
			$slug     = get_post_field( 'post_name', get_post( $forms ) );
			$filename = 'give-export-donors-' . $slug . '-' . date( 'm-d-Y' );
		} else {
			$filename = 'give-export-donors-all-forms-' . date( 'm-d-Y' );
		}

		return $filename;
	}

	/**
	 * Set the properties specific to the donors export.
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
		if ( ! empty( $this->form ) ) {
			// Cache donor ids to output unique list of donor.
			$this->query_id = give_clean( $_REQUEST['give_export_option']['query_id'] );
			$this->cache_donor_ids();
		}

		$this->price_id = ! empty( $request['give_price_option'] ) && 'all' !== $request['give_price_option'] ? absint( $request['give_price_option'] ) : null;
	}

	/**
	 * Cache donor ids.
	 *
	 * @since  1.8.9
	 * @access private
	 */
	private function cache_donor_ids() {
		// Fetch already cached donor ids.
		$donor_ids = $this->donor_ids;

		if ( $cached_donor_ids = Give_Cache::get( $this->query_id, true ) ) {
			$donor_ids = array_unique( array_merge( $cached_donor_ids, $this->donor_ids ) );
		}

		$donor_ids = array_values( $donor_ids );
		Give_Cache::set( $this->query_id, $donor_ids, HOUR_IN_SECONDS, true );
	}

	/**
	 * Set the CSV columns.
	 *
	 * @access public
	 * @since  1.5
	 * @return array|bool $cols All the columns.
	 */
	public function csv_cols() {

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
				case 'donor_created_date' :
					$cols['donor_created_date'] = esc_html__( 'Donor Created Date', 'give' );
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
	 *
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {
		$i = 0;

		$data             = array();
		$cached_donor_ids = Give_Cache::get( $this->query_id, true );

		if ( ! empty( $this->form ) ) {

			// Export donors for a specific donation form and also within specified timeframe
			$args = array(
				'output'     => 'payments', // Use 'posts' to get standard post objects
				'post_type'  => array( 'give_payment' ),
				'number'     => 30,
				'paged'      => $this->step,
				'status'     => 'publish',
				'meta_key'   => '_give_payment_form_id',
				'meta_value' => absint( $this->form ),
			);

			// Check for date option filter
			if ( ! empty( $this->data['donor_export_start_date'] ) || ! empty( $this->data['donor_export_end_date'] ) ) {
				$args['start_date'] = ! empty( $this->data['donor_export_start_date'] ) ? date( 'Y-n-d 00:00:00', strtotime( $this->data['donor_export_start_date'] ) ) : date( 'Y-n-d 23:59:59', '1970-1-01 00:00:00' );
				$args['end_date']   = ! empty( $this->data['donor_export_end_date'] ) ? date( 'Y-n-d 23:59:59', strtotime( $this->data['donor_export_end_date'] ) ) : date( 'Y-n-d 23:59:59', current_time( 'timestamp' ) );
			}

			// Check for price option.
			if ( null !== $this->price_id ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_give_payment_price_id',
						'value' => (int) $this->price_id,
					),
				);
			}

			$payments_query = new Give_Payments_Query( $args );
			$payments       = $payments_query->get_payments();

			if ( $payments ) {
				/* @var Give_Payment $payment */
				foreach ( $payments as $payment ) {
					// Set donation sum.
					$this->payment_stats[ $payment->customer_id ]['donation_sum'] = isset( $this->payment_stats[ $payment->customer_id ]['donation_sum'] ) ?
						$this->payment_stats[ $payment->customer_id ]['donation_sum'] :
						0;
					$this->payment_stats[ $payment->customer_id ]['donation_sum'] += $payment->total;

					// Set donation count.
					$this->payment_stats[ $payment->customer_id ]['donations'] = isset( $this->payment_stats[ $payment->customer_id ]['donations'] ) ?
						++ $this->payment_stats[ $payment->customer_id ]['donations'] :
						1;

					// Set donation form name.
					$this->payment_stats[ $payment->customer_id ]['form_title'] = $payment->form_title;

					// Continue if donor already included.
					if ( empty( $payment->customer_id ) ||
					     in_array( $payment->customer_id, $cached_donor_ids )
					) {
						continue;
					}

					$this->donor_ids[] = $cached_donor_ids[] = $payment->customer_id;

					$i ++;
				}

				if ( ! empty( $this->donor_ids ) ) {
					foreach ( $this->donor_ids as $donor_id ) {
						$donor                 = Give()->donors->get_donor_by( 'id', $donor_id );
						$donor->purchase_count = $this->payment_stats[ $donor_id ]['donations'];
						$donor->purchase_value = $this->payment_stats[ $donor_id ]['donation_sum'];
						$data[]                = $this->set_donor_data( $i, $data, $donor );
					}

					// Cache donor ids only if admin export donor for specific form.
					$this->cache_donor_ids();
				}
			}
		} else {

			// Export all donors.
			$offset = 30 * ( $this->step - 1 );

			$args = array(
				'number' => 30,
				'offset' => $offset,
			);

			// Check for date option filter
			if ( ! empty( $this->data['donor_export_start_date'] ) || ! empty( $this->data['donor_export_end_date'] ) ) {
				$args['date'] = array(
					'start' => ! empty( $this->data['donor_export_start_date'] ) ? date( 'Y-n-d 00:00:00', strtotime( $this->data['donor_export_start_date'] ) ) : date( 'Y-n-d 23:59:59', '1970-1-01 00:00:00' ),
					'end'   => ! empty( $this->data['donor_export_end_date'] ) ? date( 'Y-n-d 23:59:59', strtotime( $this->data['donor_export_end_date'] ) ) : date( 'Y-n-d 23:59:59', current_time( 'timestamp' ) ),
				);
			}

			$donors = Give()->donors->get_donors( $args );

			foreach ( $donors as $donor ) {

				// Continue if donor already included.
				if ( empty( $donor->id ) || empty( $donor->payment_ids ) ) {
					continue;
				}

				$data[] = $this->set_donor_data( $i, $data, $donor );
				$i ++;
			}
		}// End if().

		$data = apply_filters( 'give_export_get_data', $data );
		$data = apply_filters( "give_export_get_data_{$this->export_type}", $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$percentage = 0;

		// We can't count the number when getting them for a specific form.
		if ( empty( $this->form ) ) {

			$total = Give()->donors->count();

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
	 * @param int $i CSV line.
	 * @param array $data Donor CSV data.
	 * @param object $donor Donor data.
	 *
	 * @return mixed
	 */
	private function set_donor_data( $i, $data, $donor ) {

		$columns = $this->csv_cols();

		// Set address variable.
		$address = '';
		if ( isset( $donor->user_id ) && $donor->user_id > 0 ) {
			$address = give_get_donor_address( $donor->user_id );
		}

		// Set columns
		if ( ! empty( $columns['full_name'] ) ) {
			$donor_name              = give_get_donor_name_by( $donor->id, 'donor' );
			$data[ $i ]['full_name'] = $donor_name;
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
		if ( ! empty( $columns['donor_created_date'] ) ) {
			$data[ $i ]['donor_created_date'] = date_i18n( give_date_format(), strtotime( $donor->date_created ) );
		}
		if ( ! empty( $columns['donations'] ) ) {
			$data[ $i ]['donations'] = $donor->purchase_count;
		}
		if ( ! empty( $columns['donation_sum'] ) ) {
			$data[ $i ]['donation_sum'] = give_format_amount( $donor->purchase_value, array( 'sanitize' => false ) );
		}

		$data[ $i ] = apply_filters( 'give_export_set_donor_data', $data[ $i ], $donor );

		return $data[ $i ];

	}

	/**
	 * Unset the properties specific to the donors export.
	 *
	 * @param array $request
	 * @param Give_Batch_Export $export
	 */
	public function unset_properties( $request, $export ) {
		if ( $export->done ) {
			Give_Cache::delete( "give_cache_{$this->query_id}" );
		}
	}
}