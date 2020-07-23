<?php
namespace Give\PaymentGateways\PayPalCommerce;

use Give\Helpers\ArrayDataSet;
use InvalidArgumentException;

/**
 * Class MerchantDetail
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.8.0
 */
class MerchantDetail {
	/**
	 * PayPal merchant Id  (email address)
	 *
	 * @since 2.8.0
	 *
	 * @var null|string
	 */
	public $merchantId = null;

	/**
	 * PayPal merchant id
	 *
	 * @since 2.8.0
	 *
	 * @var null|string
	 */
	public $merchantIdInPayPal = null;

	/**
	 * Client id.
	 *
	 * @since 2.8.0
	 *
	 * @var null |string
	 */
	public $clientId = null;

	/**
	 * Client Secret
	 *
	 * @since 2.8.0
	 *
	 * @var null|string
	 */
	public $clientSecret = null;

	/**
	 * Environment mode.
	 *
	 * @var null|string
	 */
	private $mode = null;

	/**
	 * Access token.
	 *
	 * @var null|string
	 */
	public $accessToken = null;

	/**
	 * Access token.
	 *
	 * @var array
	 */
	private $tokenDetails = null;

	/**
	 * MerchantDetail constructor.
	 */
	public function __construct() {
		$this->mode = give( PayPalClient::class )->mode;
	}

	/**
	 * Define properties values.
	 *
	 * @since 2.8.0
	 *
	 * @return $this
	 */
	public function boot() {
		$paypalAccount = get_option( OptionId::$payPalAccountsOptionKey, [] );

		if ( $paypalAccount ) {
			$this->validate( $paypalAccount );
			$this->setupProperties( $paypalAccount );
		}

		return $this;
	}

	/**
	 * Save merchant details.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public function save() {
		return update_option( OptionId::$payPalAccountsOptionKey, $this->toArray() );
	}

	/**
	 * Delete merchant details.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public function delete() {
		return delete_option( OptionId::$payPalAccountsOptionKey );
	}

	/**
	 * Return array of merchnat details.
	 *
	 * @sicne 2.8.0
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'merchantId'         => $this->merchantId,
			'merchantIdInPayPal' => $this->merchantIdInPayPal,
			$this->mode          => [
				'clientId'     => $this->clientId,
				'clientSecret' => $this->clientSecret,
				'token'        => $this->tokenDetails,
			],
		];
	}

	/**
	 * Make MerchantDetail object from array.
	 *
	 * @param array $merchantDetails
	 *
	 * @since 2.8.0
	 *
	 * @return MerchantDetail
	 */
	public function fromArray( $merchantDetails ) {
		$obj = new static();

		$obj->setupProperties( $merchantDetails );

		return $obj;
	}


	/**
	 * Setup properties from array.
	 *
	 * @param $merchantDetails
	 *
	 * @since 2.8.0
	 *
	 */
	private function setupProperties( $merchantDetails ) {
		$this->merchantId         = $merchantDetails['merchantId'];
		$this->merchantIdInPayPal = $merchantDetails['merchantIdInPayPal'];

		$this->clientId     = $merchantDetails[ $this->mode ]['clientId'];
		$this->clientSecret = $merchantDetails[ $this->mode ]['clientSecret'];
		$this->tokenDetails = $merchantDetails[ $this->mode ]['token'];
		$this->accessToken  = $this->tokenDetails['accessToken'];
	}

	/**
	 * Validate merchant details.
	 *
	 * @since 2.8.0
	 *
	 * @param array $merchantDetails
	 */
	private function validate( $merchantDetails ) {
		$required = [ 'merchantId', 'merchantIdInPayPal', $this->mode ];
		$array    = array_filter( $merchantDetails ); // Remove empty values.

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException(
				sprintf(
					__( 'To create a MerchantDetail object, please provide valid merchantId, merchantIdInPayPal and %1$s', 'give' ),
					$this->mode
				)
			);
		}
	}

	/**
	 * Get refresh token code.
	 *
	 * @since 2.8.0
	 * @return mixed
	 */
	public function getRefreshToken() {
		return $this->tokenDetails['refreshToken'];
	}

	/**
	 * Get refresh token code.
	 *
	 * @param array $tokenDetails
	 *
	 * @return mixed
	 * @since 2.8.0
	 *
	 */
	public function setTokenDetails( $tokenDetails ) {
		$this->tokenDetails = array_merge( $this->tokenDetails, $tokenDetails );
	}

	/**
	 * Get client token for hosted credit card fields.
	 *
	 * @since 2.8.0
	 */
	public function getClientToken() {
		$response = wp_remote_retrieve_body(
			wp_remote_post(
				give( PayPalClient::class )->getEnvironment()->baseUrl() . '/v1/identity/generate-token',
				[
					'headers' => [
						'Accept'          => 'application/json',
						'Accept-Language' => 'en_US',
						'Authorization'   => sprintf(
							'Bearer %1$s',
							$this->accessToken
						),
						'Content-Type'    => 'application/json',
					],
				]
			)
		);

		if ( ! $response ) {
			return '';
		}

		$response = ArrayDataSet::camelCaseKeys( json_decode( $response, true ) );

		return $response['clientToken'];
	}
}
