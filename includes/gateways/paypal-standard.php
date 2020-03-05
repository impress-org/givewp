<?php
/**
 * PayPal Standard Gateway
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Toggle PayPal CC Billing Detail Fieldset.
 *
 * @param int $form_id Form ID.
 *
 * @return bool
 * @since 1.8.5
 */
function give_paypal_standard_billing_fields( $form_id ) {

	if ( give_is_setting_enabled( give_get_option( 'paypal_standard_billing_details' ) ) ) {
		give_default_cc_address_fields( $form_id );

		return true;
	}

	return false;

}

add_action( 'give_paypal_cc_form', 'give_paypal_standard_billing_fields' );

/**
 * Process PayPal Payment.
 *
 * @param array $payment_data Payment data.
 *
 * @return void
 * @since 1.0
 */
function give_process_paypal_payment( $payment_data ) {

	// Validate nonce.
	give_validate_nonce( $payment_data['gateway_nonce'], 'give-gateway' );

	$payment_id = give_create_payment( $payment_data );

	// Check payment.
	if ( empty( $payment_id ) ) {
		// Record the error.
		give_record_gateway_error(
			__( 'Payment Error', 'give' ),
			sprintf( /* translators: %s: payment data */
				__( 'Payment creation failed before sending donor to PayPal. Payment data: %s', 'give' ),
				json_encode( $payment_data )
			),
			$payment_id
		);
		// Problems? Send back.
		give_send_back_to_checkout( '?payment-mode=' . $payment_data['post_data']['give-gateway'] );
	}

	// Redirect to PayPal.
	wp_redirect( give_build_paypal_url( $payment_id, $payment_data ) );
	exit;
}

add_action( 'give_gateway_paypal', 'give_process_paypal_payment' );

/**
 * Listens for a PayPal IPN requests and then sends to the processing function.
 *
 * @return void
 * @since 1.0
 */
function give_listen_for_paypal_ipn() {

	// Regular PayPal IPN.
	if ( isset( $_GET['give-listener'] ) && 'IPN' === $_GET['give-listener'] ) {
		/**
		 * Fires while verifying PayPal IPN
		 *
		 * @since 1.0
		 */
		do_action( 'give_verify_paypal_ipn' );
	}
}

add_action( 'init', 'give_listen_for_paypal_ipn' );

/**
 * Process PayPal IPN
 *
 * @return void
 * @since 1.0
 */
function give_process_paypal_ipn() {

	// Check the request method is POST.
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	// Set initial post data to empty string.
	$post_data = '';

	// Fallback just in case post_max_size is lower than needed.
	if ( ini_get( 'allow_url_fopen' ) ) {
		$post_data = file_get_contents( 'php://input' );
	} else {
		// If allow_url_fopen is not enabled, then make sure that post_max_size is large enough.
		ini_set( 'post_max_size', '12M' );
	}
	// Start the encoded data collection with notification command.
	$encoded_data = 'cmd=_notify-validate';

	// Get current arg separator.
	$arg_separator = give_get_php_arg_separator_output();

	// Verify there is a post_data.
	if ( $post_data || strlen( $post_data ) > 0 ) {
		// Append the data.
		$encoded_data .= $arg_separator . $post_data;
	} else {
		// Check if POST is empty.
		if ( empty( $_POST ) ) {
			// Nothing to do.
			return;
		} else {
			// Loop through each POST.
			foreach ( $_POST as $key => $value ) {
				// Encode the value and append the data.
				$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
			}
		}
	}

	// Convert collected post data to an array.
	parse_str( $encoded_data, $encoded_data_array );

	foreach ( $encoded_data_array as $key => $value ) {

		if ( false !== strpos( $key, 'amp;' ) ) {
			$new_key = str_replace( '&amp;', '&', $key );
			$new_key = str_replace( 'amp;', '&', $new_key );

			unset( $encoded_data_array[ $key ] );
			$encoded_data_array[ $new_key ] = $value;
		}
	}

	$api_response = false;

	// Validate IPN request w/ PayPal if user hasn't disabled this security measure.
	if ( give_is_setting_enabled( give_get_option( 'paypal_verification' ) ) ) {

		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',

			),
			'sslverify'   => false,
			'body'        => $encoded_data_array,
		);

		// Validate the IPN.
		$api_response = wp_remote_post( give_get_paypal_redirect(), $remote_post_vars );

		if ( is_wp_error( $api_response ) ) {
			give_record_gateway_error(
				__( 'IPN Error', 'give' ),
				sprintf( /* translators: %s: Paypal IPN response */
					__( 'Invalid IPN verification response. IPN data: %s', 'give' ),
					json_encode( $api_response )
				)
			);

			return; // Something went wrong.
		}

		if ( 'VERIFIED' !== $api_response['body'] ) {
			give_record_gateway_error(
				__( 'IPN Error', 'give' ),
				sprintf( /* translators: %s: Paypal IPN response */
					__( 'Invalid IPN verification response. IPN data: %s', 'give' ),
					json_encode( $api_response )
				)
			);

			return; // Response not okay.
		}
	}// End if().

	// Check if $post_data_array has been populated.
	if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ) {
		return;
	}

	$defaults = array(
		'txn_type'       => '',
		'payment_status' => '',
	);

	$encoded_data_array = wp_parse_args( $encoded_data_array, $defaults );

	$payment_id = isset( $encoded_data_array['custom'] ) ? absint( $encoded_data_array['custom'] ) : 0;
	$txn_type   = $encoded_data_array['txn_type'];

	// Check for PayPal IPN Notifications and update data based on it.
	$current_timestamp = current_time( 'timestamp' );
	$paypal_ipn_vars   = array(
		'auth_status'    => isset( $api_response['body'] ) ? $api_response['body'] : 'N/A',
		'transaction_id' => isset( $encoded_data_array['txn_id'] ) ? $encoded_data_array['txn_id'] : 'N/A',
		'payment_id'     => $payment_id,
	);
	update_option( 'give_last_paypal_ipn_received', $paypal_ipn_vars, false );
	give_insert_payment_note(
		$payment_id,
		sprintf(
			__( 'IPN received on %1$s at %2$s', 'give' ),
			date_i18n( 'm/d/Y', $current_timestamp ),
			date_i18n( 'H:i', $current_timestamp )
		)
	);
	give_update_meta( $payment_id, 'give_last_paypal_ipn_received', $current_timestamp );

	if ( has_action( 'give_paypal_' . $txn_type ) ) {
		/**
		 * Fires while processing PayPal IPN $txn_type.
		 *
		 * Allow PayPal IPN types to be processed separately.
		 *
		 * @param array $encoded_data_array Encoded data.
		 * @param int   $payment_id         Payment id.
		 *
		 * @since 1.0
		 */
		do_action( "give_paypal_{$txn_type}", $encoded_data_array, $payment_id );
	} else {
		/**
		 * Fires while process PayPal IPN.
		 *
		 * Fallback to web accept just in case the txn_type isn't present.
		 *
		 * @param array $encoded_data_array Encoded data.
		 * @param int   $payment_id         Payment id.
		 *
		 * @since 1.0
		 */
		do_action( 'give_paypal_web_accept', $encoded_data_array, $payment_id );
	}
	exit;
}

add_action( 'give_verify_paypal_ipn', 'give_process_paypal_ipn' );

/**
 * Process web accept (one time) payment IPNs.
 *
 * @param array $data       The IPN Data.
 * @param int   $payment_id The payment ID from Give.
 *
 * @return void
 * @since 1.0
 */
function give_process_paypal_web_accept( $data, $payment_id ) {

	// Only allow through these transaction types.
	if ( 'web_accept' !== $data['txn_type'] && 'cart' !== $data['txn_type'] && 'refunded' !== strtolower( $data['payment_status'] ) ) {
		return;
	}

	// Need $payment_id to continue.
	if ( empty( $payment_id ) ) {
		return;
	}

	// Collect donation payment details.
	$paypal_amount  = $data['mc_gross'];
	$payment_status = strtolower( $data['payment_status'] );
	$currency_code  = strtolower( $data['mc_currency'] );
	$business_email = isset( $data['business'] ) && is_email( $data['business'] ) ? trim( $data['business'] ) : trim( $data['receiver_email'] );
	$payment_meta   = give_get_payment_meta( $payment_id );

	// Must be a PayPal standard IPN.
	if ( 'paypal' !== give_get_payment_gateway( $payment_id ) ) {
		return;
	}

	// Verify payment recipient.
	if ( strcasecmp( $business_email, trim( give_get_option( 'paypal_email' ) ) ) !== 0 ) {

		give_record_gateway_error(
			__( 'IPN Error', 'give' ),
			sprintf( /* translators: %s: Paypal IPN response */
				__( 'Invalid business email in IPN response. IPN data: %s', 'give' ),
				json_encode( $data )
			),
			$payment_id
		);
		give_update_payment_status( $payment_id, 'failed' );
		give_insert_payment_note( $payment_id, __( 'Payment failed due to invalid PayPal business email.', 'give' ) );

		return;
	}

	// Verify payment currency.
	if ( $currency_code !== strtolower( $payment_meta['currency'] ) ) {

		give_record_gateway_error(
			__( 'IPN Error', 'give' ),
			sprintf( /* translators: %s: Paypal IPN response */
				__( 'Invalid currency in IPN response. IPN data: %s', 'give' ),
				json_encode( $data )
			),
			$payment_id
		);
		give_update_payment_status( $payment_id, 'failed' );
		give_insert_payment_note( $payment_id, __( 'Payment failed due to invalid currency in PayPal IPN.', 'give' ) );

		return;
	}

	// Process refunds & reversed.
	if ( 'refunded' === $payment_status || 'reversed' === $payment_status ) {
		give_process_paypal_refund( $data, $payment_id );

		return;
	}

	// Only complete payments once.
	if ( 'publish' === get_post_status( $payment_id ) ) {
		return;
	}

	// Retrieve the total donation amount (before PayPal).
	$payment_amount = give_donation_amount( $payment_id );

	// Check that the donation PP and local db amounts match.
	if ( number_format( (float) $paypal_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
		// The prices don't match
		give_record_gateway_error(
			__( 'IPN Error', 'give' ),
			sprintf( /* translators: %s: Paypal IPN response */
				__( 'Invalid payment amount in IPN response. IPN data: %s', 'give' ),
				json_encode( $data )
			),
			$payment_id
		);
		give_update_payment_status( $payment_id, 'failed' );
		give_insert_payment_note( $payment_id, __( 'Payment failed due to invalid amount in PayPal IPN.', 'give' ) );

		return;
	}

	// Process completed donations.
	if ( 'completed' === $payment_status || give_is_test_mode() ) {

		give_insert_payment_note(
			$payment_id,
			sprintf( /* translators: %s: Paypal transaction ID */
				__( 'PayPal Transaction ID: %s', 'give' ),
				$data['txn_id']
			)
		);
		give_set_payment_transaction_id( $payment_id, $data['txn_id'] );
		give_update_payment_status( $payment_id, 'publish' );

	} elseif ( 'pending' === $payment_status && isset( $data['pending_reason'] ) ) {

		// Look for possible pending reasons, such as an eCheck.
		$note = give_paypal_get_pending_donation_note( $data['pending_reason'] );

		if ( ! empty( $note ) ) {
			give_insert_payment_note( $payment_id, $note );
		}
	}

}

add_action( 'give_paypal_web_accept', 'give_process_paypal_web_accept', 10, 2 );

/**
 * Process PayPal IPN Refunds
 *
 * @param array $data       IPN Data
 * @param int   $payment_id The payment ID.
 *
 * @return void
 * @since 1.0
 */
function give_process_paypal_refund( $data, $payment_id = 0 ) {

	// Collect payment details.
	if ( empty( $payment_id ) ) {
		return;
	}

	// Only refund payments once.
	if ( 'refunded' === get_post_status( $payment_id ) ) {
		return;
	}

	$payment_amount = give_donation_amount( $payment_id );
	$refund_amount  = $data['payment_gross'] * - 1;

	if ( number_format( (float) $refund_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {

		give_insert_payment_note(
			$payment_id,
			sprintf( /* translators: %s: Paypal parent transaction ID */
				__( 'Partial PayPal refund processed: %s', 'give' ),
				$data['parent_txn_id']
			)
		);

		return; // This is a partial refund

	}

	give_insert_payment_note(
		$payment_id,
		sprintf( /* translators: 1: Paypal parent transaction ID 2. Paypal reason code */
			__( 'PayPal Payment #%1$s Refunded for reason: %2$s', 'give' ),
			$data['parent_txn_id'],
			$data['reason_code']
		)
	);
	give_insert_payment_note(
		$payment_id,
		sprintf( /* translators: %s: Paypal transaction ID */
			__( 'PayPal Refund Transaction ID: %s', 'give' ),
			$data['txn_id']
		)
	);
	give_update_payment_status( $payment_id, 'refunded' );
}

/**
 * Get PayPal Redirect
 *
 * @param bool $ssl_check Is SSL?
 *
 * @return string
 * @since 1.0
 */
function give_get_paypal_redirect( $ssl_check = false ) {

	if ( is_ssl() || ! $ssl_check ) {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}

	// Check the current payment mode
	if ( give_is_test_mode() ) {
		// Test mode
		$paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';
	} else {
		// Live mode
		$paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';
	}

	return apply_filters( 'give_paypal_uri', $paypal_uri );
}

/**
 * Set the Page Style for offsite PayPal page.
 *
 * @return string
 * @since 1.0
 */
function give_get_paypal_page_style() {
	$page_style = trim( give_get_option( 'paypal_page_style', 'PayPal' ) );

	return apply_filters( 'give_paypal_page_style', $page_style );
}

/**
 * PayPal Success Page
 *
 * Shows "Donation Processing" message for PayPal payments that are still pending on site return
 *
 * @param $content
 *
 * @return string
 * @since      1.0
 */
function give_paypal_success_page_content( $content ) {

	if ( ! isset( $_GET['payment-id'] ) && ! give_get_purchase_session() ) {
		return $content;
	}

	$payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;

	if ( ! $payment_id ) {
		$session    = give_get_purchase_session();
		$payment_id = give_get_donation_id_by_key( $session['purchase_key'] );
	}

	$payment = get_post( $payment_id );
	if ( $payment && 'pending' === $payment->post_status ) {

		// Payment is still pending so show processing indicator to fix the race condition.
		ob_start();

		give_get_template_part( 'payment', 'processing' );

		$content = ob_get_clean();

	}

	return $content;

}

add_filter( 'give_payment_confirm_paypal', 'give_paypal_success_page_content' );

/**
 * Given a transaction ID, generate a link to the PayPal transaction ID details
 *
 * @param string $transaction_id The Transaction ID
 * @param int    $payment_id     The payment ID for this transaction
 *
 * @return string                 A link to the PayPal transaction details
 * @since  1.0
 */
function give_paypal_link_transaction_id( $transaction_id, $payment_id ) {

	$paypal_base_url = 'https://history.paypal.com/cgi-bin/webscr?cmd=_history-details-from-hub&id=';
	$transaction_url = '<a href="' . esc_url( $paypal_base_url . $transaction_id ) . '" target="_blank">' . $transaction_id . '</a>';

	return apply_filters( 'give_paypal_link_payment_details_transaction_id', $transaction_url );

}

add_filter( 'give_payment_details_transaction_id-paypal', 'give_paypal_link_transaction_id', 10, 2 );


/**
 * Get pending donation note.
 *
 * @param $pending_reason
 *
 * @return string
 * @since 1.6.3
 */
function give_paypal_get_pending_donation_note( $pending_reason ) {

	$note = '';

	switch ( $pending_reason ) {

		case 'echeck':
			$note = __( 'Payment made via eCheck and will clear automatically in 5-8 days.', 'give' );
			break;

		case 'address':
			$note = __( 'Payment requires a confirmed donor address and must be accepted manually through PayPal.', 'give' );
			break;

		case 'intl':
			$note = __( 'Payment must be accepted manually through PayPal due to international account regulations.', 'give' );
			break;

		case 'multi-currency':
			$note = __( 'Payment received in non-shop currency and must be accepted manually through PayPal.', 'give' );
			break;

		case 'paymentreview':
		case 'regulatory_review':
			$note = __( 'Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations.', 'give' );
			break;

		case 'unilateral':
			$note = __( 'Payment was sent to non-confirmed or non-registered email address.', 'give' );
			break;

		case 'upgrade':
			$note = __( 'PayPal account must be upgraded before this payment can be accepted.', 'give' );
			break;

		case 'verify':
			$note = __( 'PayPal account is not verified. Verify account in order to accept this donation.', 'give' );
			break;

		case 'other':
			$note = __( 'Payment is pending for unknown reasons. Contact PayPal support for assistance.', 'give' );
			break;

	} // End switch().

	return apply_filters( 'give_paypal_get_pending_donation_note', $note );

}

/**
 * Build paypal url
 *
 * @param int   $payment_id   Payment ID
 * @param array $payment_data Array of payment data.
 *
 * @return mixed|string
 */
function give_build_paypal_url( $payment_id, $payment_data ) {
	// Only send to PayPal if the pending payment is created successfully.
	$listener_url = add_query_arg( 'give-listener', 'IPN', home_url( 'index.php' ) );

	// Get the success url.
	$return_url = add_query_arg(
		array(
			'payment-confirmation' => 'paypal',
			'payment-id'           => $payment_id,

		),
		get_permalink( give_get_option( 'success_page' ) )
	);

	// Get the PayPal redirect uri.
	$paypal_redirect = trailingslashit( give_get_paypal_redirect() ) . '?';

	// Item name.
	$item_name = give_payment_gateway_item_title( $payment_data );

	// Setup PayPal API params.
	$paypal_args = array(
		'business'      => give_get_option( 'paypal_email', false ),
		'first_name'    => $payment_data['user_info']['first_name'],
		'last_name'     => $payment_data['user_info']['last_name'],
		'email'         => $payment_data['user_email'],
		'invoice'       => $payment_data['purchase_key'],
		'amount'        => $payment_data['price'],
		'item_name'     => stripslashes( $item_name ),
		'no_shipping'   => '1',
		'shipping'      => '0',
		'no_note'       => '1',
		'currency_code' => give_get_currency( $payment_id, $payment_data ),
		'charset'       => get_bloginfo( 'charset' ),
		'custom'        => $payment_id,
		'rm'            => '2',
		'return'        => $return_url,
		'cancel_return' => give_get_failed_transaction_uri( '?payment-id=' . $payment_id ),
		'notify_url'    => $listener_url,
		'page_style'    => give_get_paypal_page_style(),
		'cbt'           => get_bloginfo( 'name' ),
		'bn'            => 'givewp_SP',
	);

	// Add user address if present.
	if ( ! empty( $payment_data['user_info']['address'] ) ) {
		$default_address = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'zip'     => '',
			'country' => '',
		);

		$address = wp_parse_args( $payment_data['user_info']['address'], $default_address );

		$paypal_args['address1'] = $address['line1'];
		$paypal_args['address2'] = $address['line2'];
		$paypal_args['city']     = $address['city'];
		$paypal_args['state']    = $address['state'];
		$paypal_args['zip']      = $address['zip'];
		$paypal_args['country']  = $address['country'];
	}

	// Donations or regular transactions?
	$paypal_args['cmd'] = give_get_paypal_button_type();

	/**
	 * Filter the paypal redirect args.
	 *
	 * @param array $paypal_args  PayPal Arguments.
	 * @param array $payment_data Payment Data.
	 *
	 * @since 1.8
	 */
	$paypal_args = apply_filters( 'give_paypal_redirect_args', $paypal_args, $payment_data );

	// Build query.
	$paypal_redirect .= http_build_query( $paypal_args );

	// Fix for some sites that encode the entities.
	$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

	return $paypal_redirect;
}


/**
 * Get paypal button type.
 *
 * @return string
 * @since 1.8
 */
function give_get_paypal_button_type() {
	// paypal_button_type can be donation or standard.
	$paypal_button_type = '_donations';
	if ( 'standard' === give_get_option( 'paypal_button_type' ) ) {
		$paypal_button_type = '_xclick';
	}

	return $paypal_button_type;
}

/**
 * Update Purchase key for specific gateway.
 *
 * @param string $custom_purchase_key
 * @param string $gateway
 * @param string $purchase_key
 *
 * @return string
 * @since 2.2.4
 */
function give_paypal_purchase_key( $custom_purchase_key, $gateway, $purchase_key ) {

	if ( 'paypal' === $gateway ) {
		$invoice_id_prefix   = give_get_option( 'paypal_invoice_prefix', 'GIVE-' );
		$custom_purchase_key = $invoice_id_prefix . $purchase_key;
	}

	return $custom_purchase_key;
}

add_filter( 'give_donation_purchase_key', 'give_paypal_purchase_key', 10, 3 );


/**
 * PayPal Standard Connect button.
 *
 * This uses Stripe's Connect button but swaps the link and logo with PayPal's.
 *
 * @return string
 * @since 2.5.0
 */
function give_paypal_connect_button() {

	// Prepare Stripe Connect URL.
	$link = admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal-standard' );

	return sprintf(
		'<a href="%1$s" id="give-paypal-connect"><span>%2$s</span></a>',
		esc_url( $link ),
		esc_html__( 'Connect to PayPal', 'give' )
	);
}
