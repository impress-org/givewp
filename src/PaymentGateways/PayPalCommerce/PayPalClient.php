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
	 * Bootstrap class.
	 *
	 * @since 2.8.0
	 *
	 * @return PayPalClient
	 */
	public function boot() {
		add_action( 'give_paypal_commerce_refresh_token', [ $this, 'refreshToken' ], 10, 2 );

		return $this;
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
	 * Register cron job to refresh access token.
	 * Note: only for internal use.
	 *
	 * @param  string  $tokenExpires
	 *
	 * @since 2.8.0
	 *
	 */
	public function registerCronJobTorRefreshToken( $tokenExpires ) {
		wp_schedule_single_event(
			time() + ( $tokenExpires - 1800 ), // Refresh token before half hours of expires date.
			'give_paypal_commerce_refresh_token'
		);
	}

	/**
	 * Refresh token.
	 * Note: only for internal use
	 *
	 * @since 2.8.0
	 */
	public function refreshToken() {
		/* @var MerchantDetail $merchant */
		$merchant = give( MerchantDetail::class );

		$refreshToken  = $merchant->getRefreshToken();
		$request       = new RefreshTokenRequest(
			$this->getEnvironment(),
			$refreshToken
		);
		$request->body = [
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken,
		];

		$response     = $this->getHttpClient()->execute( $request );
		$tokenDetails = ArrayDataSet::ucWordInKeyNameComesAfterDash( $response->result );

		$merchant->setTokenDetails( $tokenDetails );
		$merchant->save();
	}
}
