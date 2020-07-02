<?php

namespace Give\Vendor\Paypal\PayPalCheckoutSdk\Core;

use Give\Vendor\Paypal\PayPalHttp\Environment;
abstract class PayPalEnvironment implements \Give\Vendor\Paypal\PayPalHttp\Environment {

	private $clientId;
	private $clientSecret;
	public function __construct( $clientId, $clientSecret ) {
		$this->clientId     = $clientId;
		$this->clientSecret = $clientSecret;
	}
	public function authorizationString() {
		return \base64_encode( $this->clientId . ':' . $this->clientSecret );
	}
}
