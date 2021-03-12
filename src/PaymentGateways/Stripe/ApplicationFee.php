<?php

namespace Give\PaymentGateways\Stripe;

use Give_License;

/**
 * Class ApplicationFee
 * @package Give\PaymentGateways\Stripe
 *
 * @unreleased
 */
class ApplicationFee {
	/**
	 * @unreleased
	 *
	 * @return bool
	 */
	public static function canAddFee() {
		return ! self::isStripeProAddonActive() || ! self::hasLicense();
	}

	/**
	 * @unreleased
	 * @return bool
	 */
	private static function isStripeProAddonActive() {
		return defined( 'GIVE_STRIPE_VERSION' );
	}

	/**
	 * @unreleased
	 * @return bool
	 */
	private static function hasLicense() {
		// Plugin slugs (which we get from givewp.com) are fixed and will never change, so we can hardcode it.
		$pluginSlug = 'give-stripe';
		return (bool) Give_License::get_license_by_plugin_dirname( $pluginSlug );
	}
}
