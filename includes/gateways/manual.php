<?php
/**
 * Manual Gateway
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manual Gateway does not need a CC form, so remove it.
 *
 * @since 1.0
 * @return void
 */
add_action( 'give_manual_cc_form', '__return_false' );

/**
 * Processes the purchase data and uses the Manual Payment gateway to record
 * the transaction in the Purchase History
 *
 * @since 1.0
 * @global      $give_options  Array of all the Give Options
 *
 * @param array $purchase_data Purchase Data
 *
 * @return void
 */
function give_manual_payment( $purchase_data ) {

	global $give_options;

	if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'give-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	/*
	* Purchase data comes in like this
	*/
	$payment_data = array(
		'price'           => $purchase_data['price'],
		'give_form_title' => $purchase_data['post_data']['give-form-title'],
		'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
		'date'            => $purchase_data['date'],
		'user_email'      => $purchase_data['user_email'],
		'purchase_key'    => $purchase_data['purchase_key'],
		'currency'        => give_get_currency(),
		'user_info'       => $purchase_data['user_info'],
		'status'          => 'pending'
	);
	// Record the pending payment
	$payment = give_insert_payment( $payment_data );

	if ( $payment ) {
		give_update_payment_status( $payment, 'publish' );
		give_send_to_success_page();
	} else {
		give_record_gateway_error( __( 'Payment Error', 'give' ), sprintf( __( 'Payment creation failed while processing a manual (free or test) purchase. Payment data: %s', 'give' ), json_encode( $payment_data ) ), $payment );
		// If errors are present, send the user back to the purchase page so they can be corrected
		give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
	}
}

add_action( 'give_gateway_manual', 'give_manual_payment' );
