<?php
namespace Give\Tracking;

use Give\Tracking\Events\DonationFormsTracking;
use Give\Tracking\Events\DonationMetricsTracking;
use Give\Tracking\Events\GivePluginSettingsTracking;
use Give\Tracking\Events\PluginsTracking;
use Give\Tracking\Events\ThemeTracking;
use Give\Tracking\Enum\EventType;
use Give\Tracking\Repositories\Settings;
use Give\Tracking\Repositories\TelemetryAccessDetails;
use Give\Tracking\TrackingData\WebsiteInfoData;
use Give_Admin_Settings;

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
	private $usageTrackingOnBoarding;

	/**
	 * @var Settings
	 */
	private $settings;

	/**
	 * @var TelemetryAccessDetails
	 */
	private $telemetryAccessDetails;

	/**
	 * @var AccessToken
	 */
	private $accessToken;

	/**
	 * @param  UsageTrackingOnBoarding  $usageTrackingOnBoarding
	 * @param  Settings  $settings
	 * @param  TelemetryAccessDetails  $telemetryAccessDetails
	 * @param  AccessToken  $accessToken
	 */
	public function __construct(
		UsageTrackingOnBoarding $usageTrackingOnBoarding,
		Settings $settings,
		TelemetryAccessDetails $telemetryAccessDetails,
		AccessToken $accessToken
	) {
		$this->usageTrackingOnBoarding = $usageTrackingOnBoarding;
		$this->settings                = $settings;
		$this->telemetryAccessDetails  = $telemetryAccessDetails;
		$this->accessToken             = $accessToken;
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

		$timestamp = '0'; // zero value disable notice permanently.
		if ( 'hide_opt_in_notice_shortly' === $_GET['give_action'] ) {
			$timestamp = DAY_IN_SECONDS * 2 + time();
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

		$this->settings->saveUsageTrackingOptionValue( 'enabled' );
		$this->usageTrackingOnBoarding->disableNotice( 0 );
		$this->accessToken->store();
		$this->recordTracks();

		wp_safe_redirect( remove_query_arg( 'give_action' ) );
		exit();
	}

	/**
	 * OptIn website to telemetry server when admin grant by changing setting.
	 *
	 * @since 2.10.0
	 *
	 * @param  array  $oldValue
	 * @param  array  $newValue
	 *
	 * @return false
	 */
	public function optInToUsageTrackingAdminGrantManually( $oldValue, $newValue ) {
		$class = __CLASS__;
		add_filter( "give_disable_hook-update_option_give_settings:{$class}@optInToUsageTrackingAdminGrantManually", '__return_true' );

		$section = isset( $_GET['section'] ) ? 'advanced-options' : '';
		if ( ! Give_Admin_Settings::is_setting_page( 'advanced', $section ) ) {
			return false;
		}

		$usageTracking = $newValue[ $this->settings->getUsageTrackingOptionKey() ] ?: 'disabled';
		$usageTracking = give_is_setting_enabled( $usageTracking );

		// Exit if already has access token.
		if ( ! $usageTracking || $this->telemetryAccessDetails->getAccessTokenOptionValue() ) {
			return false;
		}

		$this->accessToken->store();
		$this->recordTracks();

		remove_filter( "give_disable_hook-update_option_give_settings:{$class}@optInToUsageTrackingAdminGrantManually", '__return_false' );

		return true;
	}

	/**
	 * Schedule first set of tracking information.
	 *
	 * @since 2.10.0
	 */
	private function recordTracks() {
		give( DonationFormsTracking::class )->record();
		give( DonationMetricsTracking::class )->record();
		give( ThemeTracking::class )->record();
		give( GivePluginSettingsTracking::class )->record();
		give( PluginsTracking::class )->record();
	}
}
