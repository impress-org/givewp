<?php

namespace Give\Vendor\PayPal\PayPalCheckoutSdk\Core;

use Give\Vendor\PayPal\PayPalHttp\Environment;
abstract class PayPalEnvironment implements \Give\Vendor\PayPal\PayPalHttp\Environment {

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
