<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PaymentGateway;

class PayPalCommerce implements PaymentGateway {
	/**
	 * @inheritDoc
	 */
	public function getId() {
		return 'paypal-commerce';
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return __( 'PayPal Donations', 'give' );
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return __( 'Credit Card', 'give' );
	}
}
