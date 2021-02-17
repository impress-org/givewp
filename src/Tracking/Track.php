<?php
namespace Give\Tracking;

use Give\Tracking\Enum\EventType;/**
 * Class Track
 *
 * This class uses to recode tracks and send them to sever on "shutdown" action hook.
 *
 * @package Give\Tracking
 *@since 2.10.0
 */
class Track {
	/**
	 * Collection of track events.
	 *
	 * @sicne 2.10.0
	 * @var array
	 */
	private $newTracks = [];

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
	 * @param EventType $eventType
	 * @param string $trackData
	 *
	 * @since 2.10.0
	 */
	public function recordTrack( $eventType, $trackData ) {
		$id = $eventType->getValue();
		if ( array_key_exists( $id, $this->recordedTracks ) || ! $trackData ) {
			return;
		}

		$this->newTracks[ $id ] = $trackData;
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
	 * Remove tracks.
	 *
	 * @since 2.10.0
	 */
	public function remove() {
		delete_option( self::TRACK_RECORDS_OPTION_NAME );
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
