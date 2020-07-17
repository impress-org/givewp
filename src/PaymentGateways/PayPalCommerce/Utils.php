<?php
namespace Give\PaymentGateways\PaypalCommerce;

/**
 * Class Utils
 * @package Give\PaymentGateways\PaypalCommerce
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
