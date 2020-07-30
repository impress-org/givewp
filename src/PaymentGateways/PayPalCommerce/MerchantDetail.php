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
	public static function fromArray( $merchantDetails ) {
		$obj = new static();

		$obj->validate( $merchantDetails );
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
}
