<?php
namespace Give\Tracking;

use Give\Tracking\AdminSettings;
use Give\Tracking\Events\TrackTracking;

/**
 * Class Track
 *
 * This class uses to recode tracks and send them to sever on "shutdown" action hook.
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
	 * Send tracks.
	 *
	 * @since 2.10.0
	 */
	public function send() {
		if ( empty( $this->tracks ) ) {
			return;
		}

		$trackClient = new TrackClient();

		foreach ( $this->tracks as $trackId => $trackData ) {
			$trackClient->send( $trackId, $trackData );
		}
	}

	/**
	 * Record track.
	 *
	 * @param string $trackId
	 * @param array $trackData
	 *
	 * @since 2.10.0
	 */
	public function recordTrack( $trackId, $trackData ) {
		$this->tracks[ $trackId ] = $trackData;
	}
}
