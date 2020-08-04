<?php

namespace Give\PaymentGateways\PayPalCommerce\Models;

use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use InvalidArgumentException;

/**
 * Class MerchantDetail
 * @since 2.8.0
 * @package Give\PaymentGateways\PayPalCommerce
 *
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
	 * @since 2.8.0
	 *
	 * @var null|string
	 */
	private $mode = null;

	/**
	 * Access token.
	 *
	 * @since 2.8.0
	 *
	 * @var null|string
	 */
	public $accessToken = null;

	/**
	 * Whether or not the connected account is ready to process donations.
	 *
	 * @since 2.8.0
	 *
	 * @var bool
	 */
	public $accountIsReady = true;

	/**
	 * Access token.
	 *
	 * @since 2.8.0
	 *
	 * @var array
	 */
	private $tokenDetails = null;

	/**
	 * MerchantDetail constructor.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$this->mode = give( PayPalClient::class )->mode;
	}

	/**
	 * Return array of merchant details.
	 *
	 * @sicne 2.8.0
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'merchantId'         => $this->merchantId,
			'merchantIdInPayPal' => $this->merchantIdInPayPal,
			'accountIsReady'     => $this->accountIsReady,
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
	 * @since 2.8.0
	 *
	 * @param array $merchantDetails
	 *
	 * @return MerchantDetail
	 */
	public static function fromArray( $merchantDetails ) {
		$obj = new static();

		if ( ! $merchantDetails ) {
			return $obj;
		}

		$obj->validate( $merchantDetails );
		$obj->setupProperties( $merchantDetails );

		return $obj;
	}

	/**
	 * Setup properties from array.
	 *
	 * @since 2.8.0
	 *
	 * @param $merchantDetails
	 *
	 */
	private function setupProperties( $merchantDetails ) {
		$this->merchantId         = $merchantDetails['merchantId'];
		$this->merchantIdInPayPal = $merchantDetails['merchantIdInPayPal'];

		$this->clientId       = $merchantDetails[ $this->mode ]['clientId'];
		$this->clientSecret   = $merchantDetails[ $this->mode ]['clientSecret'];
		$this->tokenDetails   = $merchantDetails[ $this->mode ]['token'];
		$this->accessToken    = $this->tokenDetails['accessToken'];
		$this->accountIsReady = $merchantDetails['accountIsReady'];
	}

	/**
	 * Validate merchant details.
	 *
	 * @since 2.8.0
	 *
	 * @param array $merchantDetails
	 */
	private function validate( $merchantDetails ) {
		$required = [ 'merchantId', 'merchantIdInPayPal', 'accountIsReady', $this->mode ];
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
	 * @since 2.8.0
	 *
	 * @param array $tokenDetails
	 *
	 * @return mixed
	 */
	public function setTokenDetails( $tokenDetails ) {
		$this->tokenDetails = array_merge( $this->tokenDetails, $tokenDetails );
	}
}
