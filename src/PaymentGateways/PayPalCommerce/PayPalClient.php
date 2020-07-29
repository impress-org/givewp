<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\Helpers\ArrayDataSet;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\RefreshTokenRequest;
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
	 * Get environment.
	 *
	 * @sicne 2.8.0
	 *
	 * @return ProductionEnvironment|SandboxEnvironment
	 */
	public function getEnvironment() {
		/* @var MerchantDetail $merchant */
		$merchant = give( MerchantDetail::class );

		return 'sandbox' === $this->mode ?
			new SandboxEnvironment( $merchant->clientId, $merchant->clientSecret ) :
			new ProductionEnvironment( $merchant->clientId, $merchant->clientSecret );
	}

	/**
	 * Get http client.
	 *
	 * @since 2.8.0
	 *
	 * @return PayPalHttpClient
	 */
	public function getHttpClient() {
		return new PayPalHttpClient( $this->getEnvironment() );
	}


	/**
	 * Get api url.
	 *
	 * @param  string  $endpoint
	 *
	 * @return string
	 * @since 2.8.0
	 */
	public function getApiUrl( $endpoint ) {
		$baseUrl = $this->getEnvironment()->baseUrl();

		return "{$baseUrl}/$endpoint";
	}
}
