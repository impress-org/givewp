<?php

namespace Give\PaymentGateways\PayPalCommerce;


use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalAuth;

/**
 * Class RefreshToken
 *
 * @since 2.8.0
 */
class RefreshToken {
	/**
	 * @since 2.8.0
	 *
	 * @var MerchantDetails
	 */
	private $detailsRepository;

	/**
	 * @since 2.9.0
	 *
	 * @var PayPalAuth
	 */
	private $payPalAuth;

	/**
	 * RefreshToken constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param MerchantDetails $detailsRepository
	 * @param PayPalAuth      $payPalAuth
	 */
	public function __construct( MerchantDetails $detailsRepository, PayPalAuth $payPalAuth ) {
		$this->detailsRepository = $detailsRepository;
		$this->payPalAuth        = $payPalAuth;
	}

	/**
	 * Return cron json name which uses to refresh token.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	private function getCronJobHookName() {
		return 'give_paypal_commerce_refresh_token';
	}

	/**
	 * Register cron job to refresh access token.
	 * Note: only for internal use.
	 *
	 * @since 2.8.0
	 *
	 * @param string $tokenExpires
	 *
	 */
	public function registerCronJobToRefreshToken( $tokenExpires ) {
		wp_schedule_single_event(
			time() + ( $tokenExpires - 1800 ), // Refresh token before half hours of expires date.
			$this->getCronJobHookName()
		);
	}

	/**
	 * Delete cron job which refresh access token.
	 * Note: only for internal use.
	 *
	 * @since 2.8.0
	 *
	 */
	public function deleteRefreshTokenCronJob() {
		wp_clear_scheduled_hook( $this->getCronJobHookName() );
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

		$tokenDetails = $this->payPalAuth->getTokenFromClientCredentials( $merchant->clientId, $merchant->clientSecret );

		$merchant->setTokenDetails( $tokenDetails );
		$this->detailsRepository->save( $merchant );

		$this->registerCronJobToRefreshToken( $tokenDetails['expiresIn'] );
	}
}
