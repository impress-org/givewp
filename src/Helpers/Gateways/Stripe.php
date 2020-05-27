<?php
namespace Give\Helpers\Gateways;

/**
 * Class Stripe
 *
 * @package Give\Helpers\Gateways
 */
class Stripe {

	/**
	 * Check whether the Account is configured or not.
	 *
	 * @since  2.7.0
	 * @access public
	 *
	 * @return bool
	 */
	public static function isAccountConfigured() {
		$publishableKey = give_stripe_get_publishable_key();
		$secretKey      = give_stripe_get_secret_key();

		return ! empty( $publishableKey ) || ! empty( $secretKey );
	}
}
