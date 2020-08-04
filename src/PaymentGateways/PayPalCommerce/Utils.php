<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;

/**
 * Class Utils
 * @since 2.8.0
 * @package Give\PaymentGateways\PayPalCommerce
 *
 */
class Utils {

	/**
	 * Return whether or not PayPal account connected
	 *
	 * @since 2.8.0
	 * @return bool
	 */
	public static function isConnected() {
		return get_option( MerchantDetails::OPTION_KEY, [] );
	}
}
