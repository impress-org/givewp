<?php
/**
 * Delete Donations between a date range.
 *
 * This class handles batch processing of deleting donations between a given range.
 *
 * @package     Admin/Tools
 * @subpackage  Admin/Tools/Give_Tools_Delete_Donations
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Tools_Delete_Donations Class
 *
 * @since 2.3.0
 */
class Give_Tools_Delete_Donations extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @var string
	 * @since 2.3.0
	 */
	public $export_type = '';

	/**
	 * Allows for a non-form batch processing to be run.
	 *
	 * @since 2.3.0
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step.
	 *
	 * @since 2.3.0
	 * @var integer
	 */
	public $per_step = 30;

	/**
	 * Set the start date of donation.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	public $start_date = '';

	/**
	 * Set the end date of donation.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	public $end_date = '';

	/**
	 * Constructor.
	 *
	 * @param number $_step Step ID of the currenct batch.
	 */
	public function __construct( $_step = 1 ) {
		parent::__construct( $_step );

		$this->is_writable = true;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 2.3.0
	 * @global object $wpdb Used to query the database using the WordPress Database API
	 *
	 * @return array|bool $data The data for the CSV file
	 */
	public function get_data() {
		$items = $this->get_stored_data( 'give_temp_delete_donation_ids' );

		if ( ! is_array( $items ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $items, $offset, $this->per_step );

		if ( $step_items ) {
			foreach ( $step_items as $item ) {
				// Delete the main payment.
				give_delete_donation( absint( $item['id'] ) );
			}
			return true;
		}

		return false;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.3.0
	 * @return int
	 */
	public function get_percentage_complete() {

		$items = $this->get_stored_data( 'give_temp_delete_donation_ids' );

		if ( ! is_array( $items ) ) {
			return 100;
		}

		$total = count( $items );

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
	 * @since 2.3.0
	 *
	 * @param array $request The Form Data passed into the batch processing.
	 */
	public function set_properties( $request ) {
		$this->start_date = isset( $request['delete_donations_start_date'] ) ? sanitize_text_field( $request['delete_donations_start_date'] ) : false;
		$this->end_date   = isset( $request['delete_donations_end_date'] ) ? sanitize_text_field( $request['delete_donations_end_date'] ) : false;
	}

	/**
	 * Process a step
	 *
	 * @since 2.3.0
	 * @return bool
	 */
	public function process_step() {
		if ( ! $this->can_export() ) {
			wp_die( esc_html__( 'You do not have permission to delete donations.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			update_option( 'give_earnings_total', give_get_total_earnings( true ), false );
			Give_Cache::delete( Give_Cache::get_key( 'give_estimated_monthly_stats' ) );

			$this->delete_data( 'give_temp_delete_donation_ids' );

			$this->done = true;

			$donation_count = get_option( 'give_temp_delete_donation_count', 0 );

			$this->message = sprintf( '%1$s %2$s', $donation_count, _n( 'donation successfully deleted.', 'donations successfully deleted.', $donation_count, 'give' ) );

			delete_option( 'give_temp_delete_donation_count' );

			return false;
		}
	}

	/**
	 * Headers
	 */
	public function headers() {
		give_ignore_user_abort();
	}

	/**
	 * Perform the export
	 *
	 * @access public
	 * @since 2.3.0
	 * @return void
	 */
	public function export() {

		// Set headers.
		$this->headers();

		give_die();
	}

	/**
	 * Pre Fetch
	 */
	public function pre_fetch() {

		if ( '1' === $this->step ) {
			$this->delete_data( 'give_temp_delete_donation_ids' );
		}

		$items = get_option( 'give_temp_delete_donation_ids', false );

		if ( false === $items ) {
			$items = array();

			/**
			 * This filter can be used to modify the args supplied to
			 * Give_Payments_Query.
			 *
			 * @since 2.3.0
			 */
			$args = apply_filters(
				'give_tools_delete_donations_only_args',
				array(
					'post_status' => 'any',
					'number'      => - 1,
					'start_date'  => $this->start_date,
					'end_date'    => $this->end_date,
				)
			);

			$posts = new Give_Payments_Query( $args );

			$payments = $posts->get_payments();

			if ( ! empty( $payments ) ) {
				foreach ( $payments as $payment ) {
					$items[] = array(
						'id' => (int) $payment->ID,
					);
				}

				// Allow filtering of items to remove with an unassociative array for each item.
				// The array contains the unique ID of the item, and a 'type' for you to use in the execution of the get_data method.
				$items = apply_filters( 'give_delete_donation_items', $items );

				$this->store_data( 'give_temp_delete_donation_ids', $items );

				$donation_count = get_option( 'give_temp_delete_donation_count', 0 );

				if ( 0 === $donation_count ) {
					add_option( 'give_temp_delete_donation_count', count( $items ) );
				} else {
					$donation_count += (int) $donation_count;
					update_option( 'give_temp_delete_donation_count', $donation_count );
				}
			}
		}
	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since 2.3.0
	 *
	 * @param string $key The option_name.
	 *
	 * @return mixed Returns the data from the database
	 */
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s", $key ) );

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
	 * Give a key, store the value.
	 *
	 * @since  2.3.0
	 *
	 * @param string $key   The option_name.
	 * @param mixed  $value The value to store.
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
	 * @since 2.3.0
	 *
	 * @param string $key The option_name to delete.
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
	 * @param array             $request Form's REQUEST array.
	 * @param Give_Batch_Export $export Export object.
	 */
	public function unset_properties( $request, $export ) {
		if ( $export->done ) {

			// Delete all the donation ids.
			$this->delete_data( 'give_temp_delete_donation_ids' );
		}
	}
}
