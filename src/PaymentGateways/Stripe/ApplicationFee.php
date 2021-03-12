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
		$isStripeProAddonActive = defined( 'GIVE_STRIPE_VERSION' );

		// Plugin slugs (which we get from givewp.com) are fixed and will never change, so we can hardcode it.
		$pluginSlug = 'give-stripe';
		$hasLicense = (bool) Give_License::get_license_by_plugin_dirname( $pluginSlug );

		return ! $isStripeProAddonActive || ! $hasLicense;
	}
}
