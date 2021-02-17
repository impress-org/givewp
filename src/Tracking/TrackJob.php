<?php

namespace Give\Tracking;

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
	 * TrackJob constructor.
	 *
	 * @param  Track  $track
	 */
	public function __construct( Track $track ) {
		$this->track = $track;
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

		foreach ( $recordedTracks as $trackId => $trackData ) {
			$this->track->post( $trackId, $trackData->get() );
		}
	}
}
