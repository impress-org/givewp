<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\Helpers\Form\Template;
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
	 * @since 2.11.1 Show billing fields conditionally.
	 *
	 * @param  int  $formId  Donation Form ID.
	 */
	public function addCreditCardForm( $formId ) {
		$this->registerCustomBillingFieldsSectionLabel();
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
		remove_action( 'give_after_cc_fields', [ $this, 'addBillingFieldsSectionLabel' ], 1 );
		remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
	}

	/**
	 * @since 2.11.1
	 */
	private function registerCustomBillingFieldsSectionLabel() {
		if ( 'sequoia' !== Template::getActiveID() ) {
			return;
		}

		add_action( 'give_after_cc_fields', [ $this, 'addBillingFieldsSectionLabel' ], 1 );
	}

	/**
	 * @since 2.11.1
	 */
	public function addBillingFieldsSectionLabel() {
		echo sprintf(
			'<div class="paypal-commerce_billing_fields_section_label"><p>%1$s</p></div>',
			esc_html__( 'Billing Details', 'give' )
		);
	}
}
