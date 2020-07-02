<?php

namespace Give\Vendor\PayPal\PayPalCheckoutSdk\Core;

use Give\Vendor\PayPal\PayPalHttp\HttpRequest;
class AccessTokenRequest extends \Give\Vendor\PayPal\PayPalHttp\HttpRequest {

	public function __construct( \Give\Vendor\PayPal\PayPalCheckoutSdk\Core\PayPalEnvironment $environment, $refreshToken = null ) {
		parent::__construct( '/v1/oauth2/token', 'POST' );
		$this->headers['Authorization'] = 'Basic ' . $environment->authorizationString();
		$body                           = [ 'grant_type' => 'client_credentials' ];
		if ( ! \is_null( $refreshToken ) ) {
			$body['grant_type']    = 'refresh_token';
			$body['refresh_token'] = $refreshToken;
		}
		$this->body                    = $body;
		$this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
	}
}
