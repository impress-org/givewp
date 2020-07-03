<?php

namespace Give\PaymentGateways\PayPalCheckout;

use Give\PaymentGateways\PaymentGateway;

class PayPalCheckout implements PaymentGateway {
	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'paypal-checkout';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return __( 'PayPal Checkout', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return __( 'Credit Card', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getOptions() {
		return [];
	}
}
