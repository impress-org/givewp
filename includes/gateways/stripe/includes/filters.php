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

/**
 * This function is used to add Stripe credentials to GiveWP form.
 *
 * @param array  $form_html_tags Form HTML tags.
 * @param object $form           Form Object.
 *
 * @since 2.7.0
 *
 * @return array|bool
 */
function give_stripe_form_add_data_tag_keys( $form_html_tags, $form ) {

	// Must have a Stripe payment gateway active.
	if ( ! give_stripe_is_any_payment_method_active() ) {
		return false;
	}

	$publishable_key = give_stripe_get_publishable_key( $form->ID );
	$account_id      = give_stripe_get_connected_account_id( $form->ID );

	$form_html_tags['data-publishable-key'] = $publishable_key;
	$form_html_tags['data-account']         = $account_id;

	return $form_html_tags;
}

add_filter( 'give_form_html_tags', 'give_stripe_form_add_data_tag_keys', 0, 2 );
