<?php

namespace Give\Tracking;

use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Helpers\Track as TrackHelper;

/**
 * Class TrackJob
 *
 * @package Give\Tracking
 * @since 2.10.0
 */
class TrackJob {
	const LAST_REQUEST_OPTION_NAME = 'give_usage_tracking_last_request';

	/**
	 * @var Track
	 */
	private $track;

	/**
	 * @var TrackClient
	 */
	private $trackClient;

	/**
	 * TrackJob constructor.
	 *
	 * @param  Track  $track
	 * @param  TrackClient  $trackClient
	 */
	public function __construct( Track $track, TrackClient $trackClient ) {
		$this->track       = $track;
		$this->trackClient = $trackClient;
	}

	/**
	 * Send tracks.
	 *
	 * @since 2.10.0
	 */
	public function send() {
		$recordedTracks = $this->track->get();

		if ( ! $recordedTracks || ! TrackHelper::isTrackingEnabled() ) {
			return;
		}

		foreach ( $recordedTracks as $trackId => $className ) {
			/* @var TrackData $class */
			$class = give( $className );

			if ( $class instanceof TrackData ) {
				$this->trackClient->post( $trackId, $class->get() );
			}
		}

		update_option( self::LAST_REQUEST_OPTION_NAME, strtotime( 'today', current_time( 'timestamp' ) ) );
		$this->track->remove();
	}
}
