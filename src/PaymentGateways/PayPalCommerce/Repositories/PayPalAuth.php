<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\ConnectClient\ConnectClient;
use Give\Helpers\ArrayDataSet;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;

class PayPalAuth {
	/**
	 * @since 2.9.0
	 *
	 * @var PayPalClient
	 */
	private $payPalClient;

	/**
	 * @since 2.9.0
	 *
	 * @var ConnectClient
	 */
	private $connectClient;

	/**
	 * PayPalAuth constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param PayPalClient  $payPalClient
	 * @param ConnectClient $connectClient
	 */
	public function __construct( PayPalClient $payPalClient, ConnectClient $connectClient ) {
		$this->payPalClient  = $payPalClient;
		$this->connectClient = $connectClient;
	}

	/**
	 * Retrieves a token for the Client ID and Secret
	 *
	 * @since 2.9.0
	 *
	 * @param string $client_id
	 * @param string $client_secret
	 *
	 * @return array
	 */
	public function getTokenFromClientCredentials( $client_id, $client_secret ) {
		$auth = base64_encode( "$client_id:$client_secret" );

		$request = wp_remote_post(
			$this->payPalClient->getApiUrl( 'v1/oauth2/token' ),
			[
				'headers' => [
					'Authorization' => "Basic $auth",
					'Content-Type'  => 'application/x-www-form-urlencoded',
				],
				'body'    => [
					'grant_type' => 'client_credentials',
				],
			]
		);

		return ArrayDataSet::camelCaseKeys( json_decode( wp_remote_retrieve_body( $request ), true ) );
	}

	/**
	 * Retrieves a token from the authorization code
	 *
	 * @since 2.9.0
	 *
	 * @param string $authCode
	 * @param string $sharedId
	 * @param string $nonce
	 *
	 * @return array|null
	 */
	public function getTokenFromAuthorizationCode( $authCode, $sharedId, $nonce ) {
		$response = wp_remote_retrieve_body(
			wp_remote_post(
				$this->payPalClient->getApiUrl( 'v1/oauth2/token' ),
				[
					'headers' => [
						'Authorization' => sprintf(
							'Basic %1$s',
							base64_encode( $sharedId )
						),
						'Content-Type'  => 'application/x-www-form-urlencoded',
					],
					'body'    => [
						'grant_type'    => 'authorization_code',
						'code'          => $authCode,
						'code_verifier' => $nonce, // Seller nonce.
					],
				]
			)
		);

		return empty( $response ) ? null : ArrayDataSet::camelCaseKeys( json_decode( $response, true ) );
	}

	/**
	 * Retrieves a Partner Link for on-boarding
	 *
	 * @param $returnUrl
	 * @param $country
	 *
	 * @return array|null
	 */
	public function getSellerPartnerLink( $returnUrl, $country ) {
		$response = wp_remote_retrieve_body(
			wp_remote_post(
				sprintf(
					$this->connectClient->getApiUrl( 'paypal?mode=%1$s&request=partner-link' ),
					$this->payPalClient->mode
				),
				[
					'body' => [
						'return_url'   => $returnUrl,
						'country_code' => $country,
					],
				]
			)
		);

		return empty( $response ) ? null : json_decode( $response, true );
	}

	/**
	 * Get seller on-boarding details from seller.
	 *
	 * @since 2.9.0
	 *
	 * @param string $accessToken
	 *
	 * @param string $merchantId
	 *
	 * @return array
	 */
	public function getSellerOnBoardingDetailsFromPayPal( $merchantId, $accessToken ) {
		$request = wp_remote_post(
			$this->connectClient->getApiUrl(
				sprintf(
					'paypal?mode=%1$s&request=seller-status',
					$this->payPalClient->mode
				)
			),
			[
				'body' => [
					'merchant_id' => $merchantId,
					'token'       => $accessToken,
				],
			]
		);

		return json_decode( wp_remote_retrieve_body( $request ), true );
	}

	/**
	 * Get seller rest API credentials
	 *
	 * @since 2.9.0
	 *
	 * @param string $accessToken
	 *
	 * @return array
	 */
	public function getSellerRestAPICredentials( $accessToken ) {
		$request = wp_remote_post(
			$this->connectClient->getApiUrl(
				sprintf(
					'paypal?mode=%1$s&request=seller-credentials',
					$this->payPalClient->mode
				)
			),
			[
				'body' => [
					'token' => $accessToken,
				],
			]
		);

		return json_decode( wp_remote_retrieve_body( $request ), true );
	}
}
