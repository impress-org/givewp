<?php
/**
 * Give - Stripe Core Frontend Filters
 *
 * @since 2.5.0
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Use give_get_payment_transaction_id() first.
 *
 * Given a Payment ID, extract the transaction ID from Stripe and update the payment meta.
 *
 * @param string $payment_id Payment ID.
 *
 * @since 2.5.0
 *
 * @return string Transaction ID
 */
function give_stripe_get_payment_txn_id_fallback( $payment_id ) {

	$notes          = give_get_payment_notes( $payment_id );
	$transaction_id = '';

	foreach ( $notes as $note ) {
		if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
			$transaction_id = $match[1];
			update_post_meta( $payment_id, '_give_payment_transaction_id', $transaction_id );
			continue;
		}
	}

	return apply_filters( 'give_stripe_get_payment_txn_id_fallback', $transaction_id, $payment_id );
}

add_filter( 'give_get_payment_transaction_id-stripe', 'give_stripe_get_payment_txn_id_fallback', 10, 1 );
add_filter( 'give_get_payment_transaction_id-stripe_ach', 'give_stripe_get_payment_txn_id_fallback', 10, 1 );
