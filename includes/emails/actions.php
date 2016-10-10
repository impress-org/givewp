<?php
/**
 * Email Actions.
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Triggers a donation receipt to be sent after the payment status is updated.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return void
 */
function give_trigger_donation_receipt( $payment_id ) {
	// Make sure we don't send a receipt while editing a donation.
	if ( isset( $_POST['give-action'] ) && 'edit_payment' == $_POST['give-action'] ) {
		return;
	}

	// Send email.
	give_email_donation_receipt( $payment_id );
}

add_action( 'give_complete_donation', 'give_trigger_donation_receipt', 999, 1 );

/**
 * Resend the Email Donation Receipt. (This can be done from the Donation History Page)
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
		wp_die( esc_html__( 'You do not have permission to edit payments.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
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

	// Send a test email.
	give_email_test_donation_receipt();

	// Remove the test email query arg.
	wp_redirect( remove_query_arg( 'give_action' ) );
	exit;
}

add_action( 'give_send_test_email', 'give_send_test_email' );
