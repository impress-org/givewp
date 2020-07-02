<?php

namespace Give\Vendor\Paypal\PayPalCheckoutSdk\Core;

class ProductionEnvironment extends \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\PayPalEnvironment {

	public function __construct( $clientId, $clientSecret ) {
		parent::__construct( $clientId, $clientSecret );
	}
	public function baseUrl() {
		return 'https://api.paypal.com';
	}
}
