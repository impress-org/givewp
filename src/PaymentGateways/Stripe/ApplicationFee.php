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
	 * @see https://github.com/impress-org/givewp/issues/5555#issuecomment-759596226
	 * @unreleased
	 *
	 * @return bool
	 */
	public static function canAddFee() {
		// Plugin slug (which we get from givewp.com) and plugin name are fixed and will never change, so we can hardcode it.
		$pluginSlug = 'give-stripe';
		$pluginName = 'Give - Stripe Gateway';

		$isStripeProAddonActive = defined( 'GIVE_STRIPE_VERSION' );

		if ( $isStripeProAddonActive ) {
			return true;
		}

		$isStripeProAddonInstalled = (bool) array_filter(
			get_plugins(),
			static function( $pluginsData ) use ( $pluginName ) {
				return $pluginName === $pluginsData['Name'];
			}
		);

		if ( $isStripeProAddonInstalled ) {
			return true;
		}

		$hasLicense = (bool) Give_License::get_license_by_plugin_dirname( $pluginSlug );

		if ( $hasLicense ) {
			return true;
		}

		return false;
	}
}
