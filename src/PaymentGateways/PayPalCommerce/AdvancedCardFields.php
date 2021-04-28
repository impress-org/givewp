<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Repositories\Settings;

/**
 * Class AdvancedCardFields
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.9.0
 */
class AdvancedCardFields {
	/**
	 * @var Settings
	 */
	private $payPalDonationsSettings;

	/**
	 * AdvancedCardFields constructor.
	 *
	 * @param  Settings  $payPalDonationsSettings
	 */
	public function __construct( Settings $payPalDonationsSettings ) {
		$this->payPalDonationsSettings = $payPalDonationsSettings;
	}

	/**
	 * PayPal commerce uses smart buttons to accept payment.
	 *
	 * @since 2.9.0
	 * @unreleased Show billing fields conditionally.
	 *
	 * @param  int  $formId  Donation Form ID.
	 */
	public function addCreditCardForm( $formId ) {
		if ( ! $this->payPalDonationsSettings->canCollectBillingInformation() ) {
			$this->removeBillingField();
		}
		give_get_cc_form( $formId );
	}

	/**
	 * Remove Address Fields if user has option enabled.
	 *
	 * @since 2.9.0
	 */
	private function removeBillingField() {
		remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
	}
}
