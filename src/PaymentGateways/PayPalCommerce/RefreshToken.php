<?php
namespace Give\PaymentGateways\PayPalCommerce;


use Give\Helpers\ArrayDataSet;
use PayPalCheckoutSdk\Core\RefreshTokenRequest;

/**
 * Class RefreshToken
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.8.0
 */
class RefreshToken {
	/**
	 * Bootstrap class.
	 *
	 * @since 2.8.0
	 *
	 * @return RefreshToken
	 */
	public function boot() {
		add_action( 'give_paypal_commerce_refresh_token', [ $this, 'refreshToken' ] );

		return $this;
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
	public function registerCronJobToRefreshToken( $tokenExpires ) {
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

		/* @var PayPalClient $paypalClient */
		$paypalClient = give( PayPalClient::class );

		$refreshToken  = $merchant->getRefreshToken();
		$request       = new RefreshTokenRequest(
			$paypalClient->getEnvironment(),
			$refreshToken
		);
		$request->body = [
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken,
		];

		$response     = $paypalClient->getHttpClient()->execute( $request );
		$tokenDetails = ArrayDataSet::camelCaseKeys( (array) $response->result );

		$merchant->setTokenDetails( $tokenDetails );
		$merchant->save();

		$this->registerCronJobToRefreshToken( $tokenDetails['expiresIn'] );
	}
}
