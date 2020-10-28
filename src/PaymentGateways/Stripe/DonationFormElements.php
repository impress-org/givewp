<?php
namespace Give\PaymentGateways\Stripe;

/**
 * Class DonationFormElements
 * @package Give\PaymentGateways\Stripe
 *
 * We use this class to output HTML fields on donation form.
 *
 * @since 2.9.2
 */
class DonationFormElements {
	/**
	 * Add hidden fields to donation forms.
	 *
	 * @since 2.9.2
	 */
	public function addHiddenFields() {
		if ( give_is_gateway_active( 'stripe_checkout' ) ) {
			printf(
				'<input type="hidden" name="stripe-checkout-type" value="%1$s">',
				give_stripe_get_checkout_type()
			);
		}
	}
}
