<?php

namespace Give\Vendor\Paypal\PayPalCheckoutSdk\Core;

use Give\Vendor\Paypal\PayPalHttp\HttpRequest;
use Give\Vendor\Paypal\PayPalHttp\Injector;
use Give\Vendor\Paypal\PayPalHttp\HttpClient;
class AuthorizationInjector implements \Give\Vendor\Paypal\PayPalHttp\Injector {

	private $client;
	private $environment;
	private $refreshToken;
	public $accessToken;
	public function __construct( \Give\Vendor\Paypal\PayPalHttp\HttpClient $client, \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\PayPalEnvironment $environment, $refreshToken ) {
		$this->client       = $client;
		$this->environment  = $environment;
		$this->refreshToken = $refreshToken;
	}
	public function inject( $request ) {
		if ( ! $this->hasAuthHeader( $request ) && ! $this->isAuthRequest( $request ) ) {
			if ( \is_null( $this->accessToken ) || $this->accessToken->isExpired() ) {
				$this->accessToken = $this->fetchAccessToken();
			}
			$request->headers['Authorization'] = 'Bearer ' . $this->accessToken->token;
		}
	}
	private function fetchAccessToken() {
		$accessTokenResponse = $this->client->execute( new \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\AccessTokenRequest( $this->environment, $this->refreshToken ) );
		$accessToken         = $accessTokenResponse->result;
		return new \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\AccessToken( $accessToken->access_token, $accessToken->token_type, $accessToken->expires_in );
	}
	private function isAuthRequest( $request ) {
		return $request instanceof \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\AccessTokenRequest || $request instanceof \Give\Vendor\Paypal\PayPalCheckoutSdk\Core\RefreshTokenRequest;
	}
	private function hasAuthHeader( \Give\Vendor\Paypal\PayPalHttp\HttpRequest $request ) {
		return \array_key_exists( 'Authorization', $request->headers );
	}
}
