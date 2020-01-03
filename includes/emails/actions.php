<?php
/**
 * Email Actions.
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, GiveWP
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
	if ( ! is_numeric( $payment_id ) ) {
		return;
	}

	// Send email.
	give_email_donation_receipt( $payment_id );
}

add_action( 'give_complete_donation', 'give_trigger_donation_receipt', 999, 1 );
