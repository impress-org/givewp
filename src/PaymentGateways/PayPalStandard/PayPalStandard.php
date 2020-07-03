<?php

namespace Give\PaymentGateways\PayPalCheckout;

use Give\PaymentGateways\PaymentGateway;

class PayPalStandard implements PaymentGateway {
	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'paypal-standard';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return __( 'PayPal Standard', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return __( 'PayPal', 'give' );
	}
}
