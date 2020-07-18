<?php
namespace Give\PaymentGateways\PaypalCommerce;

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
}
