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

use Give\Helpers\Form\Utils as FormUtils;

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

	if ( FormUtils::isLegacyForm( $form_id ) ) {
		return false;
	}

	printf(
		'
		<fieldset class="no-fields">
			<div style="display: flex; justify-content: center; margin-top: 20px;">
				<svg width="250" height="66" viewBox="0 0 250 66" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M247.001 0H239.683C239.679 0 239.675 0.00133897 239.672 0.00133897L239.668 0C237.993 0 236.294 1.24922 235.819 2.83853C235.8 2.9028 235.767 2.96439 235.752 3.03134C235.752 3.03134 235.577 3.80658 235.275 5.14685L225.223 49.5056C224.991 50.5085 224.836 51.1618 224.789 51.3319L224.808 51.356C224.46 52.9065 225.417 54.1892 226.989 54.3324L227.01 54.3619H234.605C236.269 54.3619 237.958 53.1247 238.447 51.5555C238.471 51.4792 238.508 51.4082 238.526 51.3319L249.49 3.02866L249.473 3.0233C249.846 1.37106 248.753 0 247.001 0ZM209.468 43.5965C208.453 44.2191 207.38 44.752 206.257 45.2059C204.747 45.799 203.315 46.1096 201.991 46.1096C199.958 46.1096 198.396 45.8298 197.34 45.2313C196.283 44.6636 195.728 43.6768 195.746 42.2642C195.746 40.6334 196.133 39.3681 196.933 38.376C197.739 37.4213 198.937 36.6528 200.422 36.0797C201.899 35.5923 203.716 35.1907 205.813 34.9028C207.677 34.6738 211.364 34.2601 211.836 34.2574C212.308 34.2534 212.621 34.0004 212.411 35.2241C212.317 35.7557 211.253 40.0108 210.765 41.9456C210.623 42.5253 209.831 43.3702 209.468 43.5965C209.831 43.3702 209.468 43.5965 209.468 43.5965ZM223.935 13.6902C220.83 12.0031 215.973 11.1502 209.326 11.1502C206.04 11.1502 202.737 11.4073 199.422 11.908C196.99 12.2709 196.739 12.3311 195.228 12.6511C192.119 13.3099 191.639 16.3412 191.639 16.3412L190.64 20.3393C190.074 22.8966 191.57 22.7909 192.237 22.5927C193.594 22.1937 194.33 21.7974 197.1 21.1868C199.747 20.6017 202.544 20.1639 204.776 20.1813C208.049 20.1813 210.538 20.5307 212.192 21.2029C213.848 21.9058 214.668 23.1028 214.668 24.8153C214.673 25.2224 214.684 25.6066 214.533 25.9427C214.397 26.256 214.136 26.5586 213.353 26.6617C208.682 26.9322 205.329 27.3472 201.291 27.9203C197.307 28.4666 193.821 29.4025 190.907 30.6986C187.801 32.0402 185.477 33.8357 183.874 36.1105C182.315 38.3961 181.531 41.1637 181.527 44.424C181.527 47.5048 182.664 50.018 184.865 51.9688C187.091 53.8942 189.987 54.8475 193.491 54.8475C195.68 54.8328 197.394 54.6788 198.622 54.3802C199.838 54.0817 201.163 53.6572 202.563 53.0667C203.61 52.641 204.739 52.0251 205.931 51.2525C207.125 50.4772 207.951 49.9269 209.012 49.2508L209.05 49.3151L208.752 50.5964C208.75 50.6085 208.732 50.6152 208.732 50.6272L208.742 50.6513C208.401 52.1951 209.354 53.4791 210.924 53.6304L210.944 53.6572H211.083L211.088 53.6639C212.131 53.6639 215.709 53.6626 217.381 53.6572H218.549C218.626 53.6572 218.634 53.6344 218.664 53.617C220.268 53.4256 221.805 52.1536 222.167 50.6272L228.139 25.529C228.277 24.9385 228.385 24.257 228.45 23.4724C228.524 22.6797 228.612 22.0277 228.586 21.5564C228.6 18.0042 227.032 15.3785 223.935 13.6902ZM187.973 7.97333C186.954 6.04259 185.415 4.48809 183.469 3.2777C181.474 2.06999 179.103 1.2278 176.358 0.736411C173.643 0.273142 170.416 0.00803362 166.738 0L149.628 0.00803362C147.867 0.0374902 146.138 1.38445 145.731 3.04339L134.228 51.7831C133.808 53.4394 134.952 54.8198 136.682 54.8104L144.892 54.7957C146.633 54.8104 148.407 53.4394 148.818 51.7831L151.593 39.955C151.983 38.2974 153.753 36.9089 155.522 36.933H157.856C167.87 36.933 175.635 34.93 181.194 30.9427C186.741 26.9353 189.532 21.6786 189.532 15.1406C189.514 12.286 189.015 9.8813 187.973 7.97333ZM170.319 23.9146C167.868 25.6499 164.429 26.5202 160.004 26.5202H157.968C156.208 26.5403 155.071 25.1545 155.489 23.4929L157.942 13.1483C158.308 11.5121 160.094 10.1197 161.83 10.133L164.518 10.1197C167.657 10.133 170.004 10.6445 171.625 11.6608C173.219 12.6891 173.992 14.2837 174.004 16.4086C174.011 19.6475 172.78 22.1392 170.319 23.9146Z" fill="#306FC5"/>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M131.11 11.1504C129.552 11.1504 127.509 12.3854 126.541 13.8779C126.541 13.8779 116.12 31.3785 115.096 33.1273C114.54 34.0676 113.976 33.4699 113.882 33.1202C113.806 32.6949 110.652 13.9929 110.652 13.9929C110.298 12.4872 108.692 11.1947 106.684 11.2007L100.185 11.2103C98.6189 11.2103 97.6418 12.4393 97.9903 13.9246C97.9903 13.9246 102.958 41.5039 103.926 47.9902C104.409 51.5778 103.876 52.2138 103.876 52.2138L97.4364 63.2125C96.4935 64.7038 97.0096 65.9256 98.5688 65.9256L106.099 65.9196C107.659 65.9196 109.724 64.7038 110.652 63.2101L139.623 15.2159C139.623 15.2159 142.394 11.1157 139.838 11.1504C138.095 11.1744 131.11 11.1504 131.11 11.1504ZM76.7204 43.5965C75.7055 44.2191 74.6342 44.7493 73.5107 45.2046C72.0007 45.795 70.5623 46.1083 69.2421 46.1083C67.215 46.1083 65.6487 45.8272 64.5912 45.2313C63.535 44.6623 62.9781 43.6755 62.9959 42.2616C62.9959 40.6348 63.3865 39.3655 64.1855 38.3747C64.9927 37.4187 66.185 36.6488 67.6702 36.0771C69.1513 35.587 70.9734 35.1907 73.0637 34.9028C74.9285 34.6739 78.6195 34.2615 79.0898 34.2548C79.5574 34.2535 79.8723 33.9977 79.6619 35.2215C79.5711 35.7531 78.5054 40.0109 78.0172 41.9443C77.87 42.5267 77.0779 43.3716 76.7204 43.5965C77.0779 43.3716 76.7204 43.5965 76.7204 43.5965ZM91.192 13.6888C88.0868 12.0018 83.231 11.1502 76.5847 11.1502C73.2966 11.1502 69.9948 11.4046 66.6792 11.9067C64.2424 12.2669 63.9976 12.3285 62.4849 12.6485C59.3742 13.3099 58.8956 16.3386 58.8956 16.3386L57.8972 20.3393C57.332 22.8953 58.8296 22.7895 59.4938 22.5914C60.8484 22.1924 61.5882 21.7974 64.3579 21.1841C67.001 20.6017 69.8023 20.1652 72.0314 20.1799C75.3072 20.1799 77.7935 20.5281 79.4479 21.2002C81.105 21.9031 81.9205 23.1028 81.9205 24.814C81.9315 25.221 81.9397 25.6066 81.7926 25.94C81.6537 26.256 81.3896 26.5573 80.6072 26.659C75.9411 26.9335 72.5898 27.3472 68.5453 27.9203C64.5614 28.4666 61.0767 29.4025 58.1613 30.6972C55.0547 32.0388 52.7334 33.837 51.134 36.1105C49.5691 38.3947 48.7866 41.1623 48.7838 44.424C48.7838 47.5035 49.9225 50.018 52.12 51.9675C54.3479 53.8929 57.2413 54.8475 60.7452 54.8475C62.9359 54.8328 64.648 54.6761 65.8802 54.3802C67.0945 54.079 68.4216 53.6572 69.816 53.0667C70.8667 52.641 71.9929 52.0224 73.1866 51.2512C74.3775 50.4773 75.2081 49.9269 76.2725 49.2508L76.3055 49.3137L76.0071 50.5937C76.0044 50.6058 75.9879 50.6138 75.9879 50.6286L76.0003 50.6486C75.6592 52.1964 76.6095 53.4791 78.1813 53.6278L78.2006 53.6572H78.3381L78.3422 53.6666C79.3874 53.6666 82.967 53.6612 84.6351 53.6572H85.804C85.8824 53.6572 85.8934 53.6318 85.9181 53.6157C87.5257 53.4189 89.0577 52.1496 89.4235 50.6286L95.396 25.5276C95.5307 24.9385 95.6408 24.257 95.7013 23.4737C95.781 22.677 95.8677 22.0277 95.8443 21.5577C95.858 18.0028 94.2876 15.3772 91.192 13.6888ZM53.8322 7.97333C52.8132 6.04259 51.2743 4.48809 49.3284 3.2777C47.333 2.06999 44.9622 1.2278 42.2173 0.736411C39.5027 0.273142 36.2751 0.00803362 32.5978 0L15.4877 0.00803362C13.7261 0.0374902 11.9974 1.38445 11.5904 3.04339L0.0869061 51.7831C-0.332528 53.4394 0.811633 54.8198 2.54163 54.8104L10.7515 54.7957C12.4925 54.8104 14.2665 53.4394 14.6777 51.7831L17.4528 39.955C17.842 38.2974 19.6119 36.9089 21.3818 36.933H23.7155C33.7296 36.933 41.494 34.93 47.0539 30.9427C52.6 26.9353 55.3917 21.6786 55.3917 15.1406C55.3738 12.286 54.8746 9.8813 53.8322 7.97333ZM36.1787 23.9146C33.7281 25.6499 30.2887 26.5202 25.8633 26.5202H23.8281C22.0678 26.5403 20.9305 25.1545 21.3486 23.4929L23.8019 13.1483C24.1677 11.5121 25.9541 10.1197 27.6896 10.133L30.3781 10.1197C33.5163 10.133 35.8637 10.6445 37.4851 11.6608C39.0789 12.6891 39.8518 14.2837 39.8642 16.4086C39.871 19.6475 38.6402 22.1392 36.1787 23.9146Z" fill="#265697"/>
				</svg>
			</div>
			<p style="text-align: center;"><b>%1$s</b></p>
			<p style="text-align: center;">
				<b>%2$s</b> %3$s
			</p>
		</fieldset>
	',
		__( 'Make your donation quickly and securely with PayPal', 'give' ),
		__( 'How it works:', 'give' ),
		__( 'You will be redirected to PayPal to pay using your PayPal account, or with a credit or debit card. You will then be brought back to this page to view your receipt.', 'give' )
	);

	return true;

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

		$remote_post_vars = [
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => [
				'host'         => 'www.paypal.com',
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',

			],
			'sslverify'   => false,
			'body'        => $encoded_data_array,
		];

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

	$defaults = [
		'txn_type'       => '',
		'payment_status' => '',
	];

	$encoded_data_array = wp_parse_args( $encoded_data_array, $defaults );

	$payment_id = isset( $encoded_data_array['custom'] ) ? absint( $encoded_data_array['custom'] ) : 0;
	$txn_type   = $encoded_data_array['txn_type'];

	// Check for PayPal IPN Notifications and update data based on it.
	$current_timestamp = current_time( 'timestamp' );
	$paypal_ipn_vars   = [
		'auth_status'    => isset( $api_response['body'] ) ? $api_response['body'] : 'N/A',
		'transaction_id' => isset( $encoded_data_array['txn_id'] ) ? $encoded_data_array['txn_id'] : 'N/A',
		'payment_id'     => $payment_id,
	];
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
		[
			'payment-confirmation' => 'paypal',
			'payment-id'           => $payment_id,
		],
		give_get_success_page_uri()
	);

	// Get the PayPal redirect uri.
	$paypal_redirect = trailingslashit( give_get_paypal_redirect() ) . '?';

	// Item name.
	$item_name = give_payment_gateway_item_title( $payment_data );

	// Setup PayPal API params.
	$paypal_args = [
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
		'cancel_return' => give_get_failed_transaction_uri(),
		'notify_url'    => $listener_url,
		'page_style'    => give_get_paypal_page_style(),
		'cbt'           => get_bloginfo( 'name' ),
		'bn'            => 'givewp_SP',
	];

	// Add user address if present.
	if ( ! empty( $payment_data['user_info']['address'] ) ) {
		$default_address = [
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'zip'     => '',
			'country' => '',
		];

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

	ob_start(); ?>

	<script>
		function onboardedCallback(authCode, sharedId) {
			fetch('/seller-server/login-seller', {
				method: 'POST',
				headers: {
					'content-type': 'application/json'
				},
				body: JSON.stringify({
					authCode: authCode,
					sharedId: sharedId
				})
			}).then(function(res) {
				if (!response.ok) {
					alert("Something went wrong!");
				}
			});
		}
	</script>
	<a target="_blank" data-paypal-onboard-complete="onboardedCallback" href="<Action-URL>&displayMode=minibrowser" data-paypal-button="true">Sign up for PayPal</a>
	<script id="paypal-js" src="https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js"></script>

	<?php
	return ob_get_clean();

	// Prepare Stripe Connect URL.
	//  $link = admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal-standard' );
	//
	//  return sprintf(
	//      '<a href="%1$s" id="give-paypal-connect"><span>%2$s</span></a>',
	//      esc_url( $link ),
	//      esc_html__( 'Connect to PayPal', 'give' )
	//  );

}
