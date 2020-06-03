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
 * @param int $formId Form ID.
 * @param string $paymentMethod Payment gateway id.
 *
 * @since 2.7.0
 *
 * @return void
 */
function giveStripeAddDonationStripeAccount( $donationId, $formId, $paymentMethod ) {
	// Bailout, if the donation is not processed with any of the supported payment method of Stripe.
	if (
		! empty( $donationData['gateway'] ) &&
		! in_array( $paymentMethod, give_stripe_supported_payment_methods(), true )
	) {
		return;
	}

	$defaultStripeAccount        = give_stripe_get_default_account_slug( $formId );
	$defaultStripeAccountDetails = give_stripe_get_default_account( $formId );
	$stripeAccountNote           = 'connect' === $defaultStripeAccountDetails['type'] ?
		sprintf(
			'%1$s "%2$s" %3$s',
			esc_html__( 'Donation accepted with Stripe account', 'give' ),
			"{$defaultStripeAccountDetails['account_name']} ({$defaultStripeAccount})",
			esc_html__( 'using Stripe Connect.', 'give' )
		) :
		sprintf(
			'%1$s "%2$s" %3$s',
			esc_html__( 'Donation accepted with Stripe account', 'give' ),
			give_stripe_convert_slug_to_title( $defaultStripeAccount ),
			esc_html__( 'using Manual API Keys.', 'give' )
		);

	// Store essential details for donation specific stripe account.
	give_update_meta( $donationId, '_give_stripe_processed_account_slug', $defaultStripeAccount );
	give_update_meta( $donationId, '_give_stripe_processed_account_details', $defaultStripeAccountDetails );

	// Log data to donation notes.
	give_insert_payment_note( $donationId, $stripeAccountNote );
}

add_action( 'give_insert_payment', 'giveStripeAddDonationStripeAccount', 10, 2 );

/**
 * Show Stripe Account Used under donation details.
 *
 * @param int $donationId Donation ID.
 *
 * @since 2.7.0
 *
 * @return void
 */
function giveStripeDisplayProcessedStripeAccount( $donationId ) {
	$defaultAccount        = give_get_meta( $donationId, '_give_stripe_processed_account_slug', true );
	$defaultAccountDetails = give_get_meta( $donationId, '_give_stripe_processed_account_details', true );
	$account               = 'connect' === $defaultAccountDetails['type'] ?
		"{$defaultAccountDetails['account_name']} ({$defaultAccount})" :
		give_stripe_convert_slug_to_title( $defaultAccount );
	?>
	<div class="give-order-tx-id give-admin-box-inside">
		<p>
			<strong>
				<?php esc_html_e( 'Stripe Account Used:', 'give' ); ?>
			</strong><br/>
			<?php echo $account; ?>
		</p>
	</div>
	<?php
}

add_action( 'give_view_donation_details_payment_meta_after', 'giveStripeDisplayProcessedStripeAccount' );
