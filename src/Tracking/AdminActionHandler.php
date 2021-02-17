<?php
namespace Give\Tracking;

use Give\Tracking\Events\GivePluginSettingsTracking;
use Give\Tracking\Events\PluginsTracking;
use Give\Tracking\Events\ThemeTracking;
use Give\Tracking\Enum\EventType;
use Give\Tracking\Repositories\Settings;
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
	 * @param  UsageTrackingOnBoarding  $usageTrackingOnBoarding
	 * @param  Settings  $settings
	 */
	public function __construct( UsageTrackingOnBoarding $usageTrackingOnBoarding, Settings $settings ) {
		$this->usageTrackingOnBoarding = $usageTrackingOnBoarding;
		$this->settings                = $settings;
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
		$this->storeAccessToken();

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
		if ( ! $usageTracking || get_option( TrackClient::TELEMETRY_ACCESS_TOKEN ) ) {
			return false;
		}

		$this->storeAccessToken();

		remove_filter( "give_disable_hook-update_option_give_settings:{$class}@optInToUsageTrackingAdminGrantManually", '__return_false' );

		return true;
	}

	/**
	 * Store access token
	 *
	 * @since 2.10.0
	 */
	private function storeAccessToken() {
		$client = new TrackClient();

		/* @var WebsiteInfoData $dataClass */
		$dataClass = give( WebsiteInfoData::class );

		$response = $client->post( new EventType( 'create-token' ), $dataClass, [ 'blocking' => true ] );
		if ( is_wp_error( $response ) ) {
			return;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $response['success'] ) ) {
			return;
		}

		$token = $response['data']['access_token'];
		update_option( TrackClient::TELEMETRY_ACCESS_TOKEN, $token );

		// Access token saved, now send first set of tracking information.
		give( TrackJob::class )->send();
		give( ThemeTracking::class )->record();
		give( GivePluginSettingsTracking::class )->record();
		give( PluginsTracking::class )->record();
	}
}
