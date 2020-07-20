<?php

namespace Give\PaymentGateways\PayPalCommerce;

use PayPalCheckoutSdk\Core\AccessTokenRequest;
use PayPalCheckoutSdk\Core\PayPalEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

/**
 * Class PayPalClient
 * @package Give\PaymentGateways\PaypalCommerce
 *
 * @since 2.8.0
 */
class PayPalClient {
	/**
	 * Environment mode.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	public $mode = null;

	/**
	 * PayPalClient constructor.
	 */
	public function __construct() {
		$this->mode = give_is_test_mode() ? 'sandbox' : 'live';
	}

	/**
	 * Return PayPal environment
	 *
	 * @since 2.8.0
	 *
	 * @return PayPalHttpClient
	 */
	public function getClient() {
		return new PayPalHttpClient( $this->getEnvironment() );
	}

	/**
	 * Return PayPal environment
	 *
	 * @since 2.8.0
	 *
	 * @return PayPalEnvironment
	 */
	public function getEnvironment() {
		/* @var MerchantDetail $merchant */
		$merchant = give( MerchantDetail::class );

		if ( 'sandbox' === $this->mode ) {
			return new SandboxEnvironment( $merchant->clientId, $merchant->clientSecret );
		}

		return new ProductionEnvironment( $merchant->clientId, $merchant->clientSecret );
	}

	/**
	 * Get token for PayPal request.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public function getToken() {
		$request  = new AccessTokenRequest( $this->getEnvironment() );
		$response = $this->getClient()->execute( $request );

		$response = wp_remote_retrieve_body(
			wp_remote_post(
				$this->getEnvironment()->baseUrl() . '/v1/identity/generate-token',
				[
					'headers' => [
						'Authorization'   => sprintf(
							'Bearer %1$s',
							$response->result->access_token
						),
						'Content-Type'    => 'application/json',
						'Accept'          => 'application/json',
						'Accept-Language' => 'en_US',
					],
				]
			)
		);

		return json_decode( $response )->client_token;
	}
}
