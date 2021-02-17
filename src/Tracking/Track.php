<?php
namespace Give\Tracking;

use Give\Tracking\Contracts\TrackData;

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
	 * @sicne 2.10.0
	 * @var array
	 */
	private $newTracks;

	/**
	 * Recoded tracks.
	 * @var array
	 */
	private $recordedTracks;

	/**
	 * Option name to record tracks.
	 * @var string
	 */
	const  TRACK_RECORDS_OPTION_NAME = 'give_telemetry_records';

	/**
	 * Track constructor.
	 */
	public function __construct() {
		$this->recordedTracks = get_option( self::TRACK_RECORDS_OPTION_NAME, [] );
	}

	/**
	 * Record track.
	 *
	 * @param string $trackId
	 * @param TrackData $trackData
	 *
	 * @since 2.10.0
	 */
	public function recordTrack( $trackId, TrackData $trackData ) {
		if ( array_key_exists( $trackId, $this->recordedTracks ) ) {
			return;
		}

		$this->newTracks[ $trackId ] = $trackData;
	}

	/**
	 * Get new tracks.
	 *
	 * @since 2.10.0
	 *
	 * @return array
	 */
	public function get() {
		return array_merge( $this->recordedTracks, $this->newTracks );
	}

	/**
	 * Save tracks.
	 *
	 * @since 2.10.0
	 */
	public function save() {
		update_option( self::TRACK_RECORDS_OPTION_NAME, $this->get() );
	}

	/**
	 * Return whether or not new tracks registered.
	 *
	 * @since 2.10.0
	 *
	 * @return bool
	 */
	public function hasNewTracks() {
		return (bool) $this->newTracks;
	}
}
