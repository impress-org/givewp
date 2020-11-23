<?php
namespace Give\Tracking;

use Give_Cache;

/**
 * Class AdminActionHandler
 * @package Give\Tracking
 *
 * This class uses to handle actions in WP Backed.
 *
 * @since 2.10.0
 */
class AdminActionHandler {
	/**
	 * @var UsageTrackingOnBoarding
	 */
	public $usageTrackingOnBoarding;

	/**
	 * @param  UsageTrackingOnBoarding  $usageTrackingOnBoarding
	 */
	public function constructor( UsageTrackingOnBoarding $usageTrackingOnBoarding ) {
		$this->usageTrackingOnBoarding = $usageTrackingOnBoarding;
	}

	/**
	 * Bootstrap
	 *
	 * @since 2.10.0
	 */
	public function boot() {
		add_action( 'give_opt_in_into_tracking', [ $this, 'optInToUsageTracking' ] );
		add_action( 'give_opt_out_into_tracking', [ $this, 'optOutFromUsageTracking' ] );
	}

	/**
	 * Handle opt_out_into_tracking give action.
	 *
	 * @since 2.10.0
	 */
	public function optOutFromUsageTracking() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			return;
		}

		// Hide notice.
		Give_Cache::set( $this->usageTrackingOnBoarding->getNoticeOptionKey(), true, null, true );

		give_update_option( AdminSettings::USAGE_TRACKING_OPTION_NAME, 'disabled' );

		wp_safe_redirect( remove_query_arg( 'give_action' ) );
		exit();
	}

	/**
	 * Handle opt_in_into_tracking give action.
	 *
	 * @since 2.10.0
	 */
	public function optInToUsageTracking() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			return;
		}

		give_update_option( AdminSettings::USAGE_TRACKING_OPTION_NAME, 'enabled' );

		wp_safe_redirect( remove_query_arg( 'give_action' ) );
		exit();
	}
}
