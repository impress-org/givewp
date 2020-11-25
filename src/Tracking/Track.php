<?php
namespace Give\Tracking;

use Give\Tracking\AdminSettings;
use Give\Tracking\Events\TrackTracking;

/**
 * Class Track
 *
 * @since 2.10.0
 * @package Give\Tracking
 */
class Track {
	/**
	 * Collection of track events.
	 *
	 * @ssicne 2.10.0
	 * @var array
	 */
	private $tracks;

	/**
	 * Bootstrap.
	 *
	 * @siince 2.10.0
	 */
	public function boot() {
		add_action( 'shutdown', [ $this, 'send' ] );
	}

	/**
	 * Send tracks.
	 *
	 * @since 2.10.0
	 */
	public function send() {
		if ( empty( $this->tracks ) ) {
			return;
		}

		/* @var TrackClient $trackClient */
		$trackClient = give( TrackClient::class );

		foreach ( $this->tracks as $trackId => $trackData ) {
			$trackClient->send( $trackId, $trackData );
		}
	}

	/**
	 * Record track.
	 *
	 * @param $trackId
	 * @param $trackData
	 *
	 * @since 2.10.0
	 */
	public function recordTrack( $trackId, $trackData ) {
		$this->tracks[ $trackId ] = $trackData;
	}

	/**
	 * See if we should run tracking at all.
	 *
	 * @since 2.10.0
	 *
	 * @return bool True when we can track, false when we can't.
	 */
	public function isTrackingEnabled() {
		// Track data only if website is in production mode.
		if ( function_exists( 'wp_get_environment_type' ) && wp_get_environment_type() !== 'production' ) {
			return false;
		}

		// Track data only if give is in live mode.
		if ( ! give_is_setting_enabled( give_get_option( 'test_mode' ) ) ) {
			return false;
		}

		// Check if we're allowing tracking.
		$tracking = give_get_option( AdminSettings::USAGE_TRACKING_OPTION_NAME );

		return give_is_setting_enabled( $tracking );
	}
}
