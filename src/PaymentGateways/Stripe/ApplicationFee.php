<?php

namespace Give\PaymentGateways\Stripe;

use Give_License;

/**
 * Class ApplicationFee
 * @package Give\PaymentGateways\Stripe
 *
 * @see https://github.com/impress-org/givewp/issues/5555#issuecomment-759596226
 *
 * @unreleased
 */
class ApplicationFee {

	/**
	* @note Plugin slug (which we get from givewp.com) and plugin name are fixed and will never change, so we can hardcode it.
	*/

	const PluginSlug = 'give-stripe';
	const PluginName = 'Give - Stripe Gateway';

	/**
	 *
	 * @unreleased
	 *
	 * @return bool
	 */
	public static function canAddFee() {
		$gate = new static();

		return ! ( $gate->hasLicense()
			|| $gate->isStripeProAddonActive()
			|| $gate->isStripeProAddonInstalled( get_plugins() ) );
	}

	/**
	 * @return bool
	 */
	public function isStripeProAddonActive() {
		return defined( 'GIVE_STRIPE_VERSION' );
	}

	/**
	 * @param array $plugins Array of arrays of plugin data, keyed by plugin file name. See get_plugin_data().
	 *
	 * @return bool
	 */
	public function isStripeProAddonInstalled( array $plugins ) {
		return (bool) array_filter(
			$plugins,
			static function ( $plugin ) {
				return static::PluginName === $plugin['Name'];
			}
		);
	}

	/**
	 * @return bool
	 */
	public function hasLicense() {
		return (bool) Give_License::get_license_by_plugin_dirname( static::PluginSlug );
	}
}
