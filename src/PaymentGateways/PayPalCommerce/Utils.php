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
		$mode = give_is_test_mode() ? 'sandbox' : 'live';

		return isset( get_option( OptionId::$payPalAccountsOptionKey )[ $mode ] );
	}
}
