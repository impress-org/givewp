<?php

namespace Give\Vendor\Paypal\PayPalCheckoutSdk\Core;

use Give\Vendor\Paypal\PayPalHttp\HttpRequest;
class RefreshTokenRequest extends \Give\Vendor\Paypal\PayPalHttp\HttpRequest {

	public function __construct( \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\PayPalEnvironment $environment, $authorizationCode ) {
		parent::__construct( '/v1/identity/openidconnect/tokenservice', 'POST' );
		$this->headers['Authorization'] = 'Basic ' . $environment->authorizationString();
		$this->headers['Content-Type']  = 'application/x-www-form-urlencoded';
		$this->body                     = [
			'grant_type' => 'authorization_code',
			'code'       => $authorizationCode,
		];
	}
}
