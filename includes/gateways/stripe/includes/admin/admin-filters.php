<?php
/**
 * Give - Stripe Core | Admin Filters
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
 * Given a transaction ID, generate a link to the Stripe transaction ID details
 *
 * @since 2.5.0
 *
 * @param string $transaction_id The Transaction ID.
 * @param int    $payment_id The payment ID for this transaction.
 *
 * @return string                 A link to the Transaction details
 */
function give_stripe_link_transaction_id( $transaction_id, $payment_id ) {

	$url = give_stripe_get_transaction_link( $payment_id, $transaction_id );

	return apply_filters( 'give_stripe_link_donation_details_transaction_id', $url );

}

add_filter( 'give_payment_details_transaction_id-stripe', 'give_stripe_link_transaction_id', 10, 2 );
add_filter( 'give_payment_details_transaction_id-stripe_ach', 'give_stripe_link_transaction_id', 10, 2 );
