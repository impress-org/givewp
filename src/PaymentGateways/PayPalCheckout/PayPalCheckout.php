<?php

namespace Give\PaymentGateways\PayPalCheckout;

use Give\PaymentGateways\PaymentGateway;

class PayPalCheckout implements PaymentGateway {

	public function getId() {
		return 'paypal-checkout';
	}

	public function getName() {
		 return __( 'PayPal Checkout', 'give' );
	}

	public function getPaymentMethodLabel() {
		return __( 'Credit Card', 'give' );
	}
}
