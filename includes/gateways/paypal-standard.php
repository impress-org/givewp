<?php
/**
 * PayPal Standard Gateway
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/**
 * PayPal Remove CC Form
 *
 * PayPal Standard does not need a CC form, so remove it.
 *
 * @access private
 * @since  1.0
 */
add_action( 'give_paypal_cc_form', '__return_false' );

/**
 * Process PayPal Purchase
 *
 * @since 1.0
 * @global      $give_options  Array of all the Give Options
 *
 * @param array $purchase_data Purchase Data
 *
 * @return void
 */
function give_process_paypal_purchase( $purchase_data ) {
	global $give_options;

	if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'give-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	// Collect payment data
	$payment_data = array(
		'price'           => $purchase_data['price'],
		'give_form_title' => $purchase_data['post_data']['give-form-title'],
		'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
		'date'            => $purchase_data['date'],
		'user_email'      => $purchase_data['user_email'],
		'purchase_key'    => $purchase_data['purchase_key'],
		'currency'        => give_get_currency(),
		'user_info'       => $purchase_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'paypal'
	);

	// Record the pending payment
	$payment = give_insert_payment( $payment_data );

	// Check payment
	if ( ! $payment ) {
		// Record the error
		give_record_gateway_error( __( 'Payment Error', 'give' ), sprintf( __( 'Payment creation failed before sending buyer to PayPal. Payment data: %s', 'give' ), json_encode( $payment_data ) ), $payment );
		// Problems? send back
		give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
	} else {
		// Only send to PayPal if the pending payment is created successfully
		$listener_url = esc_url( add_query_arg( 'give-listener', 'IPN', home_url( 'index.php' ) ) );

		// Get the success url
		$return_url = esc_url( add_query_arg( array(
			'payment-confirmation' => 'paypal',
			'payment-id'           => $payment

		), get_permalink( $give_options['success_page'] ) ) );

		// Get the PayPal redirect uri
		$paypal_redirect = trailingslashit( give_get_paypal_redirect() ) . '?';

		// Setup PayPal arguments
		$paypal_args = array(
			'business'      => $give_options['paypal_email'],
			'email'         => $purchase_data['user_email'],
			'invoice'       => $purchase_data['purchase_key'],
			'amount'        => $purchase_data['post_data']['give-amount'],
			// The all important donation amount
			'item_name'     => $purchase_data['post_data']['give-form-title'],
			// "Purpose" field pre-populated with Form Title
			'no_shipping'   => '1',
			'shipping'      => '0',
			'no_note'       => '1',
			'currency_code' => give_get_currency(),
			'charset'       => get_bloginfo( 'charset' ),
			'custom'        => $payment,
			'rm'            => '2',
			'return'        => $return_url,
			'cancel_return' => give_get_failed_transaction_uri( '?payment-id=' . $payment ),
			'notify_url'    => $listener_url,
			'page_style'    => give_get_paypal_page_style(),
			'cbt'           => get_bloginfo( 'name' ),
			'bn'            => 'WordImpress_Donate_Give_US'
		);

		if ( ! empty( $purchase_data['user_info']['address'] ) ) {
			$paypal_args['address1'] = $purchase_data['user_info']['address']['line1'];
			$paypal_args['address2'] = $purchase_data['user_info']['address']['line2'];
			$paypal_args['city']     = $purchase_data['user_info']['address']['city'];
			$paypal_args['country']  = $purchase_data['user_info']['address']['country'];
		}

		$paypal_extra_args = array(
			'cmd' => '_donations',
		);

		$paypal_args = array_merge( $paypal_extra_args, $paypal_args );


		$paypal_args = apply_filters( 'give_paypal_redirect_args', $paypal_args, $purchase_data );

		// Build query
		$paypal_redirect .= http_build_query( $paypal_args );

		// Fix for some sites that encode the entities
		$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

		// Redirect to PayPal
		wp_redirect( $paypal_redirect );
		exit;
	}

}

add_action( 'give_gateway_paypal', 'give_process_paypal_purchase' );

/**
 * Listens for a PayPal IPN requests and then sends to the processing function
 *
 * @since 1.0
 * @global $give_options Array of all the Give Options
 * @return void
 */
function give_listen_for_paypal_ipn() {
	global $give_options;

	// Regular PayPal IPN
	if ( isset( $_GET['give-listener'] ) && $_GET['give-listener'] == 'IPN' ) {
		do_action( 'give_verify_paypal_ipn' );
	}
}

add_action( 'init', 'give_listen_for_paypal_ipn' );

/**
 * Process PayPal IPN
 *
 * @since 1.0
 * @global $give_options Array of all the Give Options
 * @return void
 */
function give_process_paypal_ipn() {
	global $give_options;

	// Check the request method is POST
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
		return;
	}

	// Set initial post data to empty string
	$post_data = '';

	// Fallback just in case post_max_size is lower than needed
	if ( ini_get( 'allow_url_fopen' ) ) {
		$post_data = file_get_contents( 'php://input' );
	} else {
		// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough
		ini_set( 'post_max_size', '12M' );
	}
	// Start the encoded data collection with notification command
	$encoded_data = 'cmd=_notify-validate';

	// Get current arg separator
	$arg_separator = give_get_php_arg_separator_output();

	// Verify there is a post_data
	if ( $post_data || strlen( $post_data ) > 0 ) {
		// Append the data
		$encoded_data .= $arg_separator . $post_data;
	} else {
		// Check if POST is empty
		if ( empty( $_POST ) ) {
			// Nothing to do
			return;
		} else {
			// Loop through each POST
			foreach ( $_POST as $key => $value ) {
				// Encode the value and append the data
				$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
			}
		}
	}

	// Convert collected post data to an array
	parse_str( $encoded_data, $encoded_data_array );

	// Get the PayPal redirect uri
	$paypal_redirect = give_get_paypal_redirect( true );

	if ( ! give_get_option( 'disable_paypal_verification' ) ) {

		// Validate the IPN

		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',

			),
			'sslverify'   => false,
			'body'        => $encoded_data_array
		);

		// Get response
		$api_response = wp_remote_post( give_get_paypal_redirect(), $remote_post_vars );

		if ( is_wp_error( $api_response ) ) {
			give_record_gateway_error( __( 'IPN Error', 'give' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'give' ), json_encode( $api_response ) ) );

			return; // Something went wrong
		}

		if ( $api_response['body'] !== 'VERIFIED' && ! isset( $give_options['disable_paypal_verification'] ) ) {
			give_record_gateway_error( __( 'IPN Error', 'give' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'give' ), json_encode( $api_response ) ) );

			return; // Response not okay
		}

	}

	// Check if $post_data_array has been populated
	if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ) {
		return;
	}

	$defaults = array(
		'txn_type'       => '',
		'payment_status' => ''
	);

	$encoded_data_array = wp_parse_args( $encoded_data_array, $defaults );

	$payment_id = isset( $encoded_data_array['custom'] ) ? absint( $encoded_data_array['custom'] ) : 0;

	if ( has_action( 'give_paypal_' . $encoded_data_array['txn_type'] ) ) {
		// Allow PayPal IPN types to be processed separately
		do_action( 'give_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $payment_id );
	} else {
		// Fallback to web accept just in case the txn_type isn't present
		do_action( 'give_paypal_web_accept', $encoded_data_array, $payment_id );
	}
	exit;
}

add_action( 'give_verify_paypal_ipn', 'give_process_paypal_ipn' );

/**
 * Process web accept (one time) payment IPNs
 *
 * @since 1.0
 * @global      $give_options Array of all the Give Options
 *
 * @param array $data         IPN Data
 *
 * @return void
 */
function give_process_paypal_web_accept_and_cart( $data, $payment_id ) {
	global $give_options;

	if ( $data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' && $data['payment_status'] != 'Refunded' ) {
		return;
	}

	if ( empty( $payment_id ) ) {
		return;
	}

	// Collect payment details
	$purchase_key   = isset( $data['invoice'] ) ? $data['invoice'] : $data['item_number'];
	$paypal_amount  = $data['mc_gross'];
	$payment_status = strtolower( $data['payment_status'] );
	$currency_code  = strtolower( $data['mc_currency'] );
	$business_email = isset( $data['business'] ) && is_email( $data['business'] ) ? trim( $data['business'] ) : trim( $data['receiver_email'] );
	$payment_meta   = give_get_payment_meta( $payment_id );


	if ( give_get_payment_gateway( $payment_id ) != 'paypal' ) {
		return; // this isn't a PayPal standard IPN
	}

	// Verify payment recipient
	if ( strcasecmp( $business_email, trim( $give_options['paypal_email'] ) ) != 0 ) {

		give_record_gateway_error( __( 'IPN Error', 'give' ), sprintf( __( 'Invalid business email in IPN response. IPN data: %s', 'give' ), json_encode( $data ) ), $payment_id );
		give_update_payment_status( $payment_id, 'failed' );
		give_insert_payment_note( $payment_id, __( 'Payment failed due to invalid PayPal business email.', 'give' ) );

		return;
	}

	// Verify payment currency
	if ( $currency_code != strtolower( $payment_meta['currency'] ) ) {

		give_record_gateway_error( __( 'IPN Error', 'give' ), sprintf( __( 'Invalid currency in IPN response. IPN data: %s', 'give' ), json_encode( $data ) ), $payment_id );
		give_update_payment_status( $payment_id, 'failed' );
		give_insert_payment_note( $payment_id, __( 'Payment failed due to invalid currency in PayPal IPN.', 'give' ) );

		return;
	}

	if ( ! give_get_payment_user_email( $payment_id ) ) {

		// This runs when a Buy Now purchase was made. It bypasses checkout so no personal info is collected until PayPal

		// No email associated with purchase, so store from PayPal
		give_update_payment_meta( $payment_id, '_give_payment_user_email', $data['payer_email'] );

		// Setup and store the customers's details
		$address            = array();
		$address['line1']   = ! empty( $data['address_street'] ) ? sanitize_text_field( $data['address_street'] ) : false;
		$address['city']    = ! empty( $data['address_city'] ) ? sanitize_text_field( $data['address_city'] ) : false;
		$address['state']   = ! empty( $data['address_state'] ) ? sanitize_text_field( $data['address_state'] ) : false;
		$address['country'] = ! empty( $data['address_country_code'] ) ? sanitize_text_field( $data['address_country_code'] ) : false;
		$address['zip']     = ! empty( $data['address_zip'] ) ? sanitize_text_field( $data['address_zip'] ) : false;

		$user_info = array(
			'id'         => '-1',
			'email'      => sanitize_text_field( $data['payer_email'] ),
			'first_name' => sanitize_text_field( $data['first_name'] ),
			'last_name'  => sanitize_text_field( $data['last_name'] ),
			'discount'   => '',
			'address'    => $address
		);

		$payment_meta['user_info'] = $user_info;
		give_update_payment_meta( $payment_id, '_give_payment_meta', $payment_meta );
	}

	if ( $payment_status == 'refunded' || $payment_status == 'reversed' ) {

		// Process a refund
		give_process_paypal_refund( $data, $payment_id );

	} else {

		if ( get_post_status( $payment_id ) == 'publish' ) {
			return; // Only complete payments once
		}

		// Retrieve the total purchase amount (before PayPal)
		$payment_amount = give_get_payment_amount( $payment_id );

		if ( number_format( (float) $paypal_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
			// The prices don't match
			give_record_gateway_error( __( 'IPN Error', 'give' ), sprintf( __( 'Invalid payment amount in IPN response. IPN data: %s', 'give' ), json_encode( $data ) ), $payment_id );
			give_update_payment_status( $payment_id, 'failed' );
			give_insert_payment_note( $payment_id, __( 'Payment failed due to invalid amount in PayPal IPN.', 'give' ) );

			return;
		}
		if ( $purchase_key != give_get_payment_key( $payment_id ) ) {
			// Purchase keys don't match
			give_record_gateway_error( __( 'IPN Error', 'give' ), sprintf( __( 'Invalid purchase key in IPN response. IPN data: %s', 'give' ), json_encode( $data ) ), $payment_id );
			give_update_payment_status( $payment_id, 'failed' );
			give_insert_payment_note( $payment_id, __( 'Payment failed due to invalid purchase key in PayPal IPN.', 'give' ) );

			return;
		}

		if ( $payment_status == 'completed' || give_is_test_mode() ) {
			give_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'give' ), $data['txn_id'] ) );
			give_set_payment_transaction_id( $payment_id, $data['txn_id'] );
			give_update_payment_status( $payment_id, 'publish' );
		}
	}
}

add_action( 'give_paypal_web_accept', 'give_process_paypal_web_accept_and_cart', 10, 2 );

/**
 * Process PayPal IPN Refunds
 *
 * @since 1.0
 *
 * @param array $data IPN Data
 *
 * @return void
 */
function give_process_paypal_refund( $data, $payment_id = 0 ) {

	// Collect payment details

	if ( empty( $payment_id ) ) {
		return;
	}

	if ( get_post_status( $payment_id ) == 'refunded' ) {
		return; // Only refund payments once
	}

	$payment_amount = give_get_payment_amount( $payment_id );
	$refund_amount  = $data['payment_gross'] * - 1;

	if ( number_format( (float) $refund_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {

		give_insert_payment_note( $payment_id, sprintf( __( 'Partial PayPal refund processed: %s', 'give' ), $data['parent_txn_id'] ) );

		return; // This is a partial refund

	}

	give_insert_payment_note( $payment_id, sprintf( __( 'PayPal Payment #%s Refunded for reason: %s', 'give' ), $data['parent_txn_id'], $data['reason_code'] ) );
	give_insert_payment_note( $payment_id, sprintf( __( 'PayPal Refund Transaction ID: %s', 'give' ), $data['txn_id'] ) );
	give_update_payment_status( $payment_id, 'refunded' );
}

/**
 * Get PayPal Redirect
 *
 * @since 1.0
 * @global     $give_options Array of all the Give Options
 *
 * @param bool $ssl_check    Is SSL?
 *
 * @return string
 */
function give_get_paypal_redirect( $ssl_check = false ) {
	global $give_options;

	if ( is_ssl() || ! $ssl_check ) {
		$protocal = 'https://';
	} else {
		$protocal = 'http://';
	}

	// Check the current payment mode
	if ( give_is_test_mode() ) {
		// Test mode
		$paypal_uri = $protocal . 'www.sandbox.paypal.com/cgi-bin/webscr';
	} else {
		// Live mode
		$paypal_uri = $protocal . 'www.paypal.com/cgi-bin/webscr';
	}

	return apply_filters( 'give_paypal_uri', $paypal_uri );
}

/**
 * Set the Page Style for PayPal Purchase page
 *
 * @since 1.0
 * @global $give_options Array of all the Give Options
 * @return string
 */
function give_get_paypal_page_style() {
	global $give_options;

	$page_style = 'PayPal';

	if ( isset( $give_options['paypal_page_style'] ) ) {
		$page_style = trim( $give_options['paypal_page_style'] );
	}

	return apply_filters( 'give_paypal_page_style', $page_style );
}

/**
 * Shows "Purchase Processing" message for PayPal payments are still pending on site return
 *
 * This helps address the Race Condition, as detailed in issue #1839
 *
 * @since 1.0
 * @return string
 */
function give_paypal_success_page_content( $content ) {

	if ( ! isset( $_GET['payment-id'] ) && ! give_get_purchase_session() ) {
		return $content;
	}

	$payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;

	if ( ! $payment_id ) {
		$session    = give_get_purchase_session();
		$payment_id = give_get_purchase_id_by_key( $session['purchase_key'] );
	}

	$payment = get_post( $payment_id );

	if ( $payment && 'pending' == $payment->post_status ) {

		// Payment is still pending so show processing indicator to fix the Race Condition, issue #
		ob_start();

		give_get_template_part( 'payment', 'processing' );

		$content = ob_get_clean();

	}

	return $content;

}

add_filter( 'give_payment_confirm_paypal', 'give_paypal_success_page_content' );

/**
 * Given a Payment ID, extract the transaction ID
 *
 * @since  1.0
 *
 * @param  string $payment_id Payment ID
 *
 * @return string                   Transaction ID
 */
function give_paypal_get_payment_transaction_id( $payment_id ) {

	$transaction_id = '';
	$notes          = give_get_payment_notes( $payment_id );

	foreach ( $notes as $note ) {
		if ( preg_match( '/^PayPal Transaction ID: ([^\s]+)/', $note->comment_content, $match ) ) {
			$transaction_id = $match[1];
			continue;
		}
	}

	return apply_filters( 'give_paypal_set_payment_transaction_id', $transaction_id, $payment_id );
}

add_filter( 'give_get_payment_transaction_id-paypal', 'give_paypal_get_payment_transaction_id', 10, 1 );

/**
 * Given a transaction ID, generate a link to the PayPal transaction ID details
 *
 * @since  1.0
 *
 * @param  string $transaction_id The Transaction ID
 * @param  int    $payment_id     The payment ID for this transaction
 *
 * @return string                 A link to the PayPal transaction details
 */
function give_paypal_link_transaction_id( $transaction_id, $payment_id ) {

	$paypal_base_url = 'https://history.paypal.com/cgi-bin/webscr?cmd=_history-details-from-hub&id=';
	$transaction_url = '<a href="' . esc_url( $paypal_base_url . $transaction_id ) . '" target="_blank">' . $transaction_id . '</a>';

	return apply_filters( 'give_paypal_link_payment_details_transaction_id', $transaction_url );

}

add_filter( 'give_payment_details_transaction_id-paypal', 'give_paypal_link_transaction_id', 10, 2 );
