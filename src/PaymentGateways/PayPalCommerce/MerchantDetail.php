<?php
namespace Give\PaymentGateways\PayPalCommerce;

use http\Exception\InvalidArgumentException;

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
	 * @var null|string
	 */
	private $mode = null;

	/**
	 * MerchantDetail constructor.
	 */
	public function __construct() {
		$this->mode = give()->make( PayPalClient::class )->mode;
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

		$this->validate( $paypalAccount );
		$this->setupProperties( $paypalAccount );

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
	 * @since 2.8.0
	 *
	 * @param $merchantDetails
	 */
	private function setupProperties( $merchantDetails ) {
		$this->merchantId         = $merchantDetails['merchantId'];
		$this->merchantIdInPayPal = $merchantDetails['merchantIdInPayPal'];

		$this->clientId     = $merchantDetails[ $this->mode ]['clientId'];
		$this->clientSecret = $merchantDetails[ $this->mode ]['clientSecret'];
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
}
