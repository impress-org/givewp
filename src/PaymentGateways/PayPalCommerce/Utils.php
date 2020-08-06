<?php

namespace Give\PaymentGateways\PayPalCommerce;

/**
 * Class Utils
 *
 * @since 2.8.0
 */
class Utils {
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
