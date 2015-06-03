<?php
/**
 * Email Actions
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Triggers Donation Receipt to be sent after the payment status is updated
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return void
 */
function give_trigger_donation_receipt( $payment_id ) {
	// Make sure we don't send a purchase receipt while editing a payment
	if ( isset( $_POST['give-action'] ) && 'edit_payment' == $_POST['give-action'] ) {
		return;
	}

	// Send email
	give_email_donation_receipt( $payment_id );
}

add_action( 'give_complete_purchase', 'give_trigger_donation_receipt', 999, 1 );

/**
 * Resend the Email Purchase Receipt. (This can be done from the Payment History page)
 *
 * @since 1.0
 *
 * @param array $data Payment Data
 *
 * @return void
 */
function give_resend_donation_receipt( $data ) {

	$purchase_id = absint( $data['purchase_id'] );

	if ( empty( $purchase_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_give_payments', $purchase_id ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	give_email_donation_receipt( $purchase_id, false );

	wp_redirect( add_query_arg( array(
		'give-message' => 'email_sent',
		'give-action'  => false,
		'purchase_id'  => false
	) ) );
	exit;
}

add_action( 'give_email_links', 'give_resend_donation_receipt' );

/**
 * Trigger the sending of a Test Email
 *
 * @since 1.0
 *
 * @param array $data Parameters sent from Settings page
 *
 * @return void
 */
function give_send_test_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'give-test-email' ) ) {
		return;
	}

	// Send a test email
	give_email_test_donation_receipt();

	// Remove the test email query arg
	wp_redirect( remove_query_arg( 'give_action' ) );
	exit;
}

add_action( 'give_send_test_email', 'give_send_test_email' );
