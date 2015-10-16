<?php
/**
 * Payments Export Class
 *
 * This class handles payment export
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Payments_Export Class
 *
 * @since 1.0
 */
class Give_Payments_Export extends Give_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'payments';

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

		$month = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' );
		$year  = isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'give_payments_export_filename', 'give-export-' . $this->export_type . '-' . $month . '-' . $year ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since  1.4.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		global $give_options;

		$cols = array(
			'id'       => __( 'ID', 'give' ), // unaltered payment ID (use for querying)
			'seq_id'   => __( 'Payment Number', 'give' ), // sequential payment ID
			'email'    => __( 'Email', 'give' ),
			'first'    => __( 'First Name', 'give' ),
			'last'     => __( 'Last Name', 'give' ),
			'address1' => __( 'Address', 'give' ),
			'address2' => __( 'Address (Line 2)', 'give' ),
			'city'     => __( 'City', 'give' ),
			'state'    => __( 'State', 'give' ),
			'country'  => __( 'Country', 'give' ),
			'zip'      => __( 'Zip Code', 'give' ),
			'amount'   => __( 'Donation Amount', 'give' ) . ' (' . html_entity_decode( give_currency_filter( '' ) ) . ')',
			'form_id'  => __( 'Form ID', 'give' ),
			'form'     => __( 'Form Title', 'give' ),
			'gateway'  => __( 'Payment Method', 'give' ),
			'trans_id' => __( 'Transaction ID', 'give' ),
			'key'      => __( 'Purchase Key', 'give' ),
			'date'     => __( 'Date', 'give' ),
			'user'     => __( 'User', 'give' ),
			'status'   => __( 'Status', 'give' )
		);

		if ( ! give_get_option( 'enable_sequential' ) ) {
			unset( $cols['seq_id'] );
		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  1.0
	 * @global object $wpdb Used to query the database using the WordPress
	 *                      Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb, $give_options;

		$data = array();

		$payments = give_get_payments( array(
			'offset' => 0,
			'number' => - 1,
			'mode'   => give_is_test_mode() ? 'test' : 'live',
			'status' => isset( $_POST['give_export_payment_status'] ) ? $_POST['give_export_payment_status'] : 'any',
			'month'  => isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' ),
			'year'   => isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' )
		) );

		foreach ( $payments as $payment ) {
			$payment_meta = give_get_payment_meta( $payment->ID );
			$user_info    = give_get_payment_meta_user_info( $payment->ID );
			$total        = give_get_payment_amount( $payment->ID );
			$user_id      = isset( $user_info['id'] ) && $user_info['id'] != - 1 ? $user_info['id'] : $user_info['email'];

			$form_id = isset($payment_meta['form_id']) ? $payment_meta['form_id'] : '';
			$form_title = isset($payment_meta['form_title']) ? $payment_meta['form_title'] : '';

			if ( is_numeric( $user_id ) ) {
				$user = get_userdata( $user_id );
			} else {
				$user = false;
			}

			$data[] = array(
				'id'       => $payment->ID,
				'seq_id'   => give_get_payment_number( $payment->ID ),
				'email'    => $payment_meta['email'],
				'first'    => $user_info['first_name'],
				'last'     => $user_info['last_name'],
				'address1' => isset( $user_info['address']['line1'] ) ? $user_info['address']['line1'] : '',
				'address2' => isset( $user_info['address']['line2'] ) ? $user_info['address']['line2'] : '',
				'city'     => isset( $user_info['address']['city'] ) ? $user_info['address']['city'] : '',
				'state'    => isset( $user_info['address']['state'] ) ? $user_info['address']['state'] : '',
				'country'  => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
				'zip'      => isset( $user_info['address']['zip'] ) ? $user_info['address']['zip'] : '',
				'amount'   => html_entity_decode( give_format_amount( $total ) ),
				'form_id'  => $form_id,
				'form'     => $form_title,
				'gateway'  => give_get_gateway_admin_label( get_post_meta( $payment->ID, '_give_payment_gateway', true ) ),
				'trans_id' => give_get_payment_transaction_id( $payment->ID ),
				'key'      => $payment_meta['key'],
				'date'     => $payment->post_date,
				'user'     => $user ? $user->display_name : __( 'guest', 'give' ),
				'status'   => give_get_payment_status( $payment, true )
			);

		}

		$data = apply_filters( 'give_export_get_data', $data );
		$data = apply_filters( 'give_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
