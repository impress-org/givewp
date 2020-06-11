<?php
/**
 * Give - Stripe Frontend Actions
 *
 * @since 2.5.0
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
use Give\Helpers\Gateways\Stripe;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add an errors div per form.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of Donation Arguments.
 *
 * @access public
 * @since  2.5.0
 *
 * @return void
 */
function give_stripe_add_stripe_errors( $form_id, $args ) {
	echo '<div id="give-stripe-payment-errors-' . esc_html( $args['id_prefix'] ) . '"></div>';
}

add_action( 'give_donation_form_after_cc_form', 'give_stripe_add_stripe_errors', 8899, 2 );

/**
 * Add secret source field to apply the source generated on donation submit.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of arguments.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_add_secret_payment_method_field( $form_id, $args ) {

	$id_prefix = ! empty( $args['id_prefix'] ) ? $args['id_prefix'] : 0;

	echo sprintf(
		'<input id="give-stripe-payment-method-%1$s" type="hidden" name="give_stripe_payment_method" value="">',
		esc_html( $id_prefix )
	);

}
add_action( 'give_donation_form_top', 'give_stripe_add_secret_payment_method_field', 10, 2 );

/**
 * This function is used to add Stripe account used while processing donation.
 *
 * @param int   $donationId   Donation ID.
 * @param array $donationData Donation data.
 *
 * @since 2.7.0
 *
 * @return void
 */
function giveStripeAddDonationStripeAccount( $donationId, $donationData ) {
	$paymentMethod = give_get_payment_gateway( $donationId );
	$formId        = (int) $donationData['give_form_id'];

	// Return, if the donation is not processed with any of the supported payment method of Stripe.
	if ( ! Stripe::isDonationPaymentMethod( $paymentMethod ) ) {
		return;
	}

	Stripe::addAccountDetail( $donationId, $formId );
}

add_action( 'give_insert_payment', 'giveStripeAddDonationStripeAccount', 10, 2 );
