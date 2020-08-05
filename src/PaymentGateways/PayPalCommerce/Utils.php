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
		return get_option( MerchantDetails::ACCOUNT_OPTION_KEY, [] );
	}

	/**
	 * Returns whether or not the PayPal Commerce gateway is active
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public static function gatewayIsActive() {
		return give_is_gateway_active( PayPalCommerce::GATEWAY_ID );
	}
}
