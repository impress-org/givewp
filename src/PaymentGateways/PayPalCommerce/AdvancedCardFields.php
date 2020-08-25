<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;

class AdvancedCardFields {
	/**
	 * PayPal commerce uses smart buttons to accept payment.
	 *
	 * @since 2.8.0
	 *
	 * @param int  $formId Donation Form ID.
	 *
	 * @access public
	 * @return string $form
	 *
	 */
	public function addCreditCardForm( $formId ) {
		$this->removeBillingField();
		give_get_cc_form( $formId );
	}

	/**
	 * Remove Address Fields if user has option enabled.
	 *
	 * @since 2.8.0
	 */
	private function removeBillingField() {
		remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
	}
}
