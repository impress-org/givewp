<?php

namespace Give\TestData;

use Give\TestData\Addons\Funds\ServiceProvider as Funds;
use Give\TestData\Addons\CurrencySwitcher\ServiceProvider as CurrencySwitcher;
use Give\TestData\Addons\FeeRecovery\ServiceProvider as FeeRecovery;
use Give\TestData\Addons\RecurringDonations\ServiceProvider as RecurringDonations;
use Give\TestData\Addons\ManualDonations\ServiceProvider as ManualDonations;

/**
 * Class Addons
 * @package Give\TestData\Helpers
 */
class Addons {
	/**
	 * Get add-ons
	 *
	 * @return array[]
	 * @since 1.0.0
	 */
	public static function getAddons() {
		return [
			[
				'isActive'        => defined( 'GIVE_FUNDS_VERSION' ),
				'serviceProvider' => Funds::class,
			],
			[
				'isActive'        => defined( 'GIVE_CURRENCY_SWITCHER_VERSION' ),
				'serviceProvider' => CurrencySwitcher::class,
			],
			[
				'isActive'        => defined( 'GIVE_RECURRING_VERSION' ),
				'serviceProvider' => RecurringDonations::class,
			],
			[
				'isActive'        => defined( 'GIVE_FEE_RECOVERY_VERSION' ),
				'serviceProvider' => FeeRecovery::class,
			],
			[
				'isActive'        => defined( 'GIVE_MD_VERSION' ),
				'serviceProvider' => ManualDonations::class,
			],
			[
				'isActive'        => defined( 'GIVE_FUNDS_ADDON_VERSION' ),
				'serviceProvider' => Funds::class,
			],
		];
	}

	/**
	 * Get active add-ons
	 * @return array[]
	 * @since 1.0.0
	 */
	public static function getActiveAddons() {
		return array_filter(
			self::getAddons(),
			function ( $addon ) {
				return $addon['isActive'];
			}
		);
	}
}
