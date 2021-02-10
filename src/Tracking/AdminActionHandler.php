<?php
namespace Give\Tracking;

use Give\Tracking\TrackingData\ServerData;
use Give\Tracking\TrackingData\WebsiteData;
use Give\Tracking\ValueObjects\EventId;

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
	public function __construct( UsageTrackingOnBoarding $usageTrackingOnBoarding ) {
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

		$timestamp = '0';
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

		give_update_option( AdminSettings::USAGE_TRACKING_OPTION_NAME, 'enabled' );
		$this->usageTrackingOnBoarding->disableNotice( 'permanently' );
		$this->storeAccessToken();

		wp_safe_redirect( remove_query_arg( 'give_action' ) );
		exit();
	}

	/**
	 * Store access token
	 *
	 * @since 2.10.0
	 */
	private function storeAccessToken() {
		$client = new TrackClient();
		$data   = array_merge(
			( new ServerData() )->get(),
			( new WebsiteData() )->get()
		);

		$response = $client->send( EventId::CREATE_TOKEN, $data, [ 'blocking' => true ] );
		if ( is_wp_error( $response ) ) {
			return;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $response['success'] ) ) {
			return;
		}

		$token = $response['data']['access_token'];
		update_option( 'give_telemetry_server_access_token', $token );
	}
}
