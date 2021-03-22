<?php
namespace Give\Tracking;

use Give\Tracking\Enum\EventType;
use Give\Tracking\Repositories\TrackEvents;

/**
 * Class TrackRegisterer
 *
 * This class uses to recode tracks and send them to sever on "shutdown" action hook.
 *
 * @package Give\Tracking
 * @since 2.10.0
 */
class TrackRegisterer {
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
	 * Track constructor.
	 *
	 * @param  TrackEvents  $trackEvents
	 */
	public function __construct( TrackEvents $trackEvents ) {
		$this->recordedTracks = $trackEvents->getTrackList();
	}

	/**
	 * Register track.
	 *
	 * @param EventType $eventType
	 * @param string $trackData
	 *
	 * @since 2.10.0
	 */
	public function register( $eventType, $trackData ) {
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
	public function getTrackList() {
		return array_merge( $this->recordedTracks, $this->newTracks );
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
