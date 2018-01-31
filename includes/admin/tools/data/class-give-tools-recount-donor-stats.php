<?php
/**
 * Recount all donor stats.
 *
 * This class handles batch processing of recounting all donor stats.
 *
 * @subpackage  Admin/Tools/Give_Tools_Recount_Donor_Stats
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Tools_Recount_Donor_Stats Class
 *
 * @since 1.5
 */
class Give_Tools_Recount_Donor_Stats extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
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
	public $per_step = 5;

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
	 * @return array|bool $data The data for the CSV file
	 */
	public function get_data() {

		$args = array(
			'number'  => $this->per_step,
			'offset'  => $this->per_step * ( $this->step - 1 ),
			'orderby' => 'id',
			'order'   => 'DESC',
		);

		$donors = Give()->donors->get_donors( $args );

		if ( $donors ) {

			$allowed_payment_status = apply_filters( 'give_recount_donors_donation_statuses', give_get_payment_status_keys() );

			foreach ( $donors as $donor ) {

				$attached_payment_ids = explode( ',', $donor->payment_ids );

				$attached_args = array(
					'post__in' => $attached_payment_ids,
					'number'   => - 1,
					'status'   => $allowed_payment_status,
				);

				$attached_payments = (array) give_get_payments( $attached_args );

				$unattached_args = array(
					'post__not_in' => $attached_payment_ids,
					'number'       => - 1,
					'status'       => $allowed_payment_status,
					'meta_query'   => array(
						array(
							'key'     => '_give_payment_donor_email',
							'value'   => $donor->email,
							'compare' => '=',
						),
					),
				);

				$unattached_payments = give_get_payments( $unattached_args );

				$payments = array_merge( $attached_payments, $unattached_payments );

				$purchase_value = 0.00;
				$purchase_count = 0;
				$payment_ids    = array();

				if ( $payments ) {

					foreach ( $payments as $payment ) {

						$should_process_payment = 'publish' == $payment->post_status ? true : false;
						$should_process_payment = apply_filters( 'give_donor_recount_should_process_donation', $should_process_payment, $payment );

						if ( true === $should_process_payment ) {

							if ( apply_filters( 'give_donor_recount_should_increase_value', true, $payment ) ) {
								$purchase_value += (float) give_donation_amount( $payment->ID, array( 'type' => 'stats' ) );
							}

							if ( apply_filters( 'give_donor_recount_should_increase_count', true, $payment ) ) {
								$purchase_count ++;
							}
						}

						$payment_ids[] = $payment->ID;
					}
				}

				$payment_ids = implode( ',', $payment_ids );

				$donor_update_data = array(
					'purchase_count' => $purchase_count,
					'purchase_value' => $purchase_value,
					'payment_ids'    => $payment_ids,
				);

				$donor_instance = new Give_Donor( $donor->id );
				$donor_instance->update( $donor_update_data );

			}// End foreach().

			return true;
		}// End if().

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$args = array(
			'number'  => - 1,
			'orderby' => 'id',
			'order'   => 'DESC',
		);

		$donors = Give()->donors->get_donors( $args );
		$total     = count( $donors );

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
	}

	/**
	 * Process a step
	 *
	 * @since 1.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( esc_html__( 'You do not have permission to recount stats.', 'give' ), esc_html__( 'Error', 'give' ), array(
				'response' => 403,
			) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;

			return true;
		} else {
			$this->done    = true;
			$this->message = esc_html__( 'Donor stats have been successfully recounted.', 'give' );

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
	 * @since 1.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		give_die();
	}

}
