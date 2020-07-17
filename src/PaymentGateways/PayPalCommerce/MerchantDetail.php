<?php
namespace Give\PaymentGateways\PayPalCommerce;

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
	 * Define properties values.
	 *
	 * @since 2.8.0
	 *
	 * @return $this
	 */
	public function boot() {
		$paypalAccount = get_option( OptionId::$payPalAccountsOptionKey, [] );

		if ( ! $paypalAccount ) {
			return $this;
		}

		$this->merchantId         = $paypalAccount['merchantId'];
		$this->merchantIdInPayPal = $paypalAccount['merchantIdInPayPal'];

		$mode = give_is_test_mode() ? 'sandbox' : 'live';

		$this->clientId     = $paypalAccount[ $mode ]['client_id'];
		$this->clientSecret = $paypalAccount[ $mode ]['client_secret'];

		return $this;
	}
}
