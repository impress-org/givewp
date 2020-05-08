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
		$is_configured   = false;
		$publishable_key = give_stripe_get_publishable_key();
		$secret_key      = give_stripe_get_secret_key();

		if ( ! empty( $publishable_key ) || ! empty( $secret_key ) ) {
			$is_configured = true;
		}

		return $is_configured;
	}
}
