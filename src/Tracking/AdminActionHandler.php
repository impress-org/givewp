<?php
namespace Give\Tracking;

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
	 * Handle opt_out_into_tracking give action.
	 *
	 * @since 2.10.0
	 */
	public function optOutFromUsageTracking() {
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			return;
		}

		$timestamp = 'permanently';
		if ( 'hide_opt_in_notice_shortly' === $_GET['give_action'] ) {
			$timestamp = 'shortly';
		}

		$this->usageTrackingOnBoarding->disableNotice( $timestamp );

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
		$this->usageTrackingOnBoarding->disableNotice( 'permanently' );

		wp_safe_redirect( remove_query_arg( 'give_action' ) );
		exit();
	}
}
