<?php
namespace Give\Tracking\Helpers;

use Give\Tracking\Repositories\Settings;

/**
 * Class Track
 *
 * This class contains helpers functions related to tracks
 *
 * @package Give\Tracking\Helpers
 * @since 2.10.0
 */
class Track {
	/**
	 * Return whether or not admin opted in for usage tracking.
	 *
	 * @since 2.10.0
	 *
	 * @return bool True when we can track, false when we can't.
	 */
	public static function isTrackingEnabled() {
		if ( ! self::checkEnvironment() ) {
			return false;
		}

		// Check if we're allowing tracking.
		/* @var Settings $settings */
		$settings = give( Settings::class );
		$tracking = $settings->getUsageTrackingOptionValue();

		return give_is_setting_enabled( $tracking );
	}

	/**
	 * Return whether or not environment for tracking satisfied.
	 *
	 * @return bool
	 */
	public static function checkEnvironment() {
		// Track data only if website is in production mode.
		if ( function_exists( 'wp_get_environment_type' ) && wp_get_environment_type() !== 'production' ) {
			return false;
		}

		// Track data only if give is in live mode.
		if ( give_is_setting_enabled( give_get_option( 'test_mode' ) ) ) {
			return false;
		}

		return true;
	}
}
