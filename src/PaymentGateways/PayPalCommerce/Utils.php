<?php
namespace Give\PaymentGateways\PayPalCommerce;

/**
 * Class Utils
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.8.0
 */
class Utils {

	/**
	 * Return whether or not PayPal account connected
	 *
	 * @return bool
	 * @since 2.8.0
	 */
	public static function isConnected() {
		return get_option( OptionId::$payPalAccountsOptionKey, [] );
	}
}
