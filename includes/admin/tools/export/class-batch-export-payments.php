<?php
/**
 * Payments Export Class.
 *
 * This class handles payment export in batches.
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
 * Give_Batch_Payments_Export Class
 *
 * @since 1.5
 */
class Give_Batch_Payments_Export extends Give_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 * @var string
	 * @since 1.5
	 */
	public $export_type = 'payments';

	/**
	 * Set the CSV columns.
	 *
	 * @access public
	 * @since 1.5
	 * @return array $cols All the columns.
	 */
	public function csv_cols() {
		$cols = array(
			'id'        => __( 'ID', 'give' ), // unaltered payment ID (use for querying).
			'seq_id'    => __( 'Payment Number', 'give' ), // sequential payment ID.
			'email'     => __( 'Email', 'give' ),
			'first'     => __( 'First Name', 'give' ),
			'last'      => __( 'Last Name', 'give' ),
			'address1'  => __( 'Address 1', 'give' ),
			'address2'  => __( 'Address 2', 'give' ),
			'city'      => __( 'City', 'give' ),
			'state'     => __( 'State', 'give' ),
			'country'   => __( 'Country', 'give' ),
			'zip'       => __( 'Zip / Postal Code', 'give' ),
			'form_id'   => __( 'Form ID', 'give' ),
			'form_name' => __( 'Form Name', 'give' ),
			'amount'    => __( 'Amount', 'give' ) . ' (' . give_currency_symbol( '', true ) . ')',
			'gateway'   => __( 'Payment Method', 'give' ),
			'trans_id'  => __( 'Transaction ID', 'give' ),
			'key'       => __( 'Key', 'give' ),
			'date'      => __( 'Date', 'give' ),
			'user'      => __( 'User', 'give' ),
			'status'    => __( 'Status', 'give' )
		);

		if ( ! give_get_option( 'enable_sequential' ) ) {
			unset( $cols['seq_id'] );
		}

		return $cols;
	}

	/**
	 * Get the Export Data.
	 *
	 * @access public
	 * @since  1.5
	 * @global object $wpdb Used to query the database using the WordPress database API.
	 * @return array $data The data for the CSV file.
	 */
	public function get_data() {

		$data = array();

		$args = array(
			'number' => 30,
			'page'   => $this->step,
			'status' => $this->status
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {

			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d 00:00:00', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d 23:59:59', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);

		}

		// Add category or tag to payment query if any.
		if ( ! empty( $this->categories ) || ! empty( $this->tags ) ) {
			$form_args = array(
				'post_type'      => 'give_forms',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
				'tax_query'      => array(
					'relation' => 'AND',
				),
			);


			if ( ! empty( $this->categories ) ) {
				$form_args['tax_query'][] = array(
					'taxonomy' => 'give_forms_category',
					'terms'    => $this->categories,
				);
			}

			if ( ! empty( $this->tags ) ) {
				$form_args['tax_query'][] = array(
					'taxonomy' => 'give_forms_tag',
					'terms'    => $this->tags,
				);
			}

			$forms = new WP_Query( $form_args );

			if ( empty( $forms->posts ) ) {
				return array();
			}

			$args['give_forms'] = $forms->posts;
		}

		$payments = give_get_payments( $args );

		if ( $payments ) {

			foreach ( $payments as $payment ) {
				$payment_meta = give_get_payment_meta( $payment->ID );
				$user_info    = give_get_payment_meta_user_info( $payment->ID );
				$total        = give_get_payment_amount( $payment->ID );
				$user_id      = isset( $user_info['id'] ) && $user_info['id'] != - 1 ? $user_info['id'] : $user_info['email'];

				if ( is_numeric( $user_id ) ) {
					$user = get_userdata( $user_id );
				} else {
					$user = false;
				}

				$data[] = array(
					'id'        => $payment->ID,
					'seq_id'    => give_get_payment_number( $payment->ID ),
					'email'     => $payment_meta['email'],
					'first'     => $user_info['first_name'],
					'last'      => $user_info['last_name'],
					'address1'  => isset( $user_info['address']['line1'] ) ? $user_info['address']['line1'] : '',
					'address2'  => isset( $user_info['address']['line2'] ) ? $user_info['address']['line2'] : '',
					'city'      => isset( $user_info['address']['city'] ) ? $user_info['address']['city'] : '',
					'state'     => isset( $user_info['address']['state'] ) ? $user_info['address']['state'] : '',
					'country'   => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
					'zip'       => isset( $user_info['address']['zip'] ) ? $user_info['address']['zip'] : '',
					'form_id'   => isset( $payment_meta['form_id'] ) ? $payment_meta['form_id'] : '',
					'form_name' => isset( $payment_meta['form_title'] ) ? $payment_meta['form_title'] : '',
					'amount'    => html_entity_decode( give_format_amount( $total, array( 'sanitize' => false ) ) ),
					'gateway'   => give_get_gateway_admin_label( give_get_meta( $payment->ID, '_give_payment_gateway', true ) ),
					'trans_id'  => give_get_payment_transaction_id( $payment->ID ),
					'key'       => $payment_meta['key'],
					'date'      => $payment->post_date,
					'user'      => $user ? $user->display_name : __( 'guest', 'give' ),
					'status'    => give_get_payment_status( $payment, true )
				);

			}

			$data = apply_filters( 'give_export_get_data', $data );
			$data = apply_filters( "give_export_get_data_{$this->export_type}", $data );

			return $data;

		}

		return array();

	}

	/**
	 * Return the calculated completion percentage.
	 *
	 * @since 1.5
	 * @return int
	 */
	public function get_percentage_complete() {

		$status = $this->status;
		$args   = array(
			'start-date' => date( 'n/d/Y', strtotime( $this->start ) ),
			'end-date'   => date( 'n/d/Y', strtotime( $this->end ) ),
		);

		if ( 'any' == $status ) {

			$total = array_sum( (array) give_count_payments( $args ) );

		} else {

			$total = give_count_payments( $args )->$status;

		}

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export.
	 *
	 * @since 1.5
	 *
	 * @param array $request The Form Data passed into the batch processing.
	 */
	public function set_properties( $request ) {
		$this->start      = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end        = isset( $request['end'] ) ? sanitize_text_field( $request['end'] ) : '';
		$this->status     = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'complete';
		$this->categories = isset( $request['give_forms_categories'] ) ? give_clean( $request['give_forms_categories'] ) : array();
		$this->tags       = isset( $request['give_forms_tags'] ) ? give_clean( $request['give_forms_tags'] ) : array();
	}
}
