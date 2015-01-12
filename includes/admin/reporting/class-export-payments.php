<?php
/**
 * Payments Export Class
 *
 * This class handles payment export
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Payments_Export Class
 *
 * @since 1.4.4
 */
class EDD_Payments_Export extends EDD_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.4.4
	 */
	public $export_type = 'payments';

	/**
	 * Set the export headers
	 *
	 * @access public
	 * @since 1.6
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
			set_time_limit( 0 );

		$month = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' );
		$year  = isset( $_POST['year']  ) ? absint( $_POST['year']  ) : date( 'Y' );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'give_payments_export_filename', 'give-export-' . $this->export_type . '-' . $month . '-' . $year ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 1.4.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		global $give_options;

		$cols = array(
			'id'       => __( 'ID',   'give' ), // unaltered payment ID (use for querying)
			'seq_id'   => __( 'Payment Number',   'give' ), // sequential payment ID
			'email'    => __( 'Email', 'give' ),
			'first'    => __( 'First Name', 'give' ),
			'last'     => __( 'Last Name', 'give' ),
			'address1' => __( 'Address', 'give' ),
			'address2' => __( 'Address (Line 2)', 'give' ),
			'city'     => __( 'City', 'give' ),
			'state'    => __( 'State', 'give' ),
			'country'  => __( 'Country', 'give' ),
			'zip'      => __( 'Zip Code', 'give' ),
			'products' => __( 'Products', 'give' ),
			'skus'     => __( 'SKUs', 'give' ),
			'amount'   => __( 'Amount', 'give' ) . ' (' . html_entity_decode( give_currency_filter( '' ) ) . ')',
			'tax'      => __( 'Tax', 'give' ) . ' (' . html_entity_decode( give_currency_filter( '' ) ) . ')',
			'discount' => __( 'Discount Code', 'give' ),
			'gateway'  => __( 'Payment Method', 'give' ),
			'trans_id' => __( 'Transaction ID', 'give' ),
			'key'      => __( 'Purchase Key', 'give' ),
			'date'     => __( 'Date', 'give' ),
			'user'     => __( 'User', 'give' ),
			'status'   => __( 'Status', 'give' )
		);

		if( ! give_use_skus() ){
			unset( $cols['skus'] );
		}
		if ( ! give_get_option( 'enable_sequential' ) ) {
			unset( $cols['seq_id'] );
		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 1.4.4
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb, $give_options;

		$data = array();

		$payments = give_get_payments( array(
			'offset' => 0,
			'number' => -1,
			'mode'   => give_is_test_mode() ? 'test' : 'live',
			'status' => isset( $_POST['give_export_payment_status'] ) ? $_POST['give_export_payment_status'] : 'any',
			'month'  => isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' ),
			'year'   => isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' )
		) );

		foreach ( $payments as $payment ) {
			$payment_meta   = give_get_payment_meta( $payment->ID );
			$user_info      = give_get_payment_meta_user_info( $payment->ID );
			$downloads      = give_get_payment_meta_cart_details( $payment->ID );
			$total          = give_get_payment_amount( $payment->ID );
			$user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
			$products       = '';
			$skus           = '';

			if ( $downloads ) {
				foreach ( $downloads as $key => $download ) {
					// Download ID
					$id = isset( $payment_meta['cart_details'] ) ? $download['id'] : $download;

					// If the download has variable prices, override the default price
					$price_override = isset( $payment_meta['cart_details'] ) ? $download['price'] : null;

					$price = give_get_download_final_price( $id, $user_info, $price_override );

					// Display the Downoad Name
					$products .= get_the_title( $id ) . ' - ';

					if ( give_use_skus() ) {
						$sku = give_get_download_sku( $id );

						if ( ! empty( $sku ) )
							$skus .= $sku;
					}

					if ( isset( $downloads[ $key ]['item_number'] ) && isset( $downloads[ $key ]['item_number']['options'] ) ) {
						$price_options = $downloads[ $key ]['item_number']['options'];

						if ( isset( $price_options['price_id'] ) ) {
							$products .= give_get_price_option_name( $id, $price_options['price_id'], $payment->ID ) . ' - ';
						}
					}
					$products .= html_entity_decode( give_currency_filter( $price ) );

					if ( $key != ( count( $downloads ) -1 ) ) {
						$products .= ' / ';

						if( give_use_skus() )
							$skus .= ' / ';
					}
				}
			}

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
				'address1' => isset( $user_info['address']['line1'] )   ? $user_info['address']['line1']   : '',
				'address2' => isset( $user_info['address']['line2'] )   ? $user_info['address']['line2']   : '',
				'city'     => isset( $user_info['address']['city'] )    ? $user_info['address']['city']    : '',
				'state'    => isset( $user_info['address']['state'] )   ? $user_info['address']['state']   : '',
				'country'  => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
				'zip'      => isset( $user_info['address']['zip'] )     ? $user_info['address']['zip']     : '',
				'products' => $products,
				'skus'     => $skus,
				'amount'   => html_entity_decode( give_format_amount( $total ) ),
				'tax'      => html_entity_decode( give_format_amount( give_get_payment_tax( $payment->ID, $payment_meta ) ) ),
				'discount' => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'give' ),
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
