<?php

namespace Give\PaymentGateways\Stripe;

use Give_License;
use Give\PaymentGateways\Stripe\Models\AccountDetail as AccountDetailModel;

/**
 * Class ApplicationFee
 * @package Give\PaymentGateways\Stripe
 *
 * @see https://github.com/impress-org/givewp/issues/5555#issuecomment-759596226
 *
 * @since 2.10.2
 */
class ApplicationFee {
	/**
	 * Slug of the Stripe add-on on GiveWP.com
	 */
	const PluginSlug = 'give-stripe';

	/**
	 * Name of the Stripe add-on on GiveWP.com
	 */
	const PluginName = 'Give - Stripe Gateway';

	/**
	 * @var AccountDetailModel
	 */
	private $accountDetail;

	/**
	 * ApplicationFee constructor.
	 *
	 * @param  AccountDetailModel  $accountDetail
	 */
	public function __construct( AccountDetailModel $accountDetail ) {
		$this->accountDetail = $accountDetail;
	}

	/**
	 * Returns true or false based on whether the Stripe fee should be applied or not
	 *
	 * @since 2.10.2
	 * @return bool
	 */
	public static function canAddFee() {
		/* @var self $gate */
		$gate = give( static::class );
		return $gate->doesCountrySupportApplicationFee()
			   && ! ( $gate->isStripeProAddonActive() || $gate->isStripeProAddonInstalled( get_plugins() ) || $gate->hasLicense() );
	}

	/**
	 * Returns true or false based on whether the Stripe Pro add-on is activated
	 *
	 * @since 2.10.2
	 *
	 * @return bool
	 */
	public function isStripeProAddonActive() {
		return defined( 'GIVE_STRIPE_VERSION' );
	}

	/**
	 * Returns true or false based on whether the plugin is installed (but not necessarily active)
	 *
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
	 * Returns true or false based on whether a license has been provided for the Stripe add-on
	 *
	 * @since 2.10.2
	 *
	 * @return bool
	 */
	public function hasLicense() {
		return (bool) Give_License::get_license_by_plugin_dirname( static::PluginSlug );
	}

	/**
	 * Return whether or not country support application fee.
	 *
	 * @since 2.10.2
	 *
	 * @return bool
	 */
	public function doesCountrySupportApplicationFee() {
		return 'BR' !== $this->accountDetail->accountCountry;
	}
}
