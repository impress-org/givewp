<?php

namespace Give\Tracking;

use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Enum\EventType;
use Give\Tracking\Helpers\Track as TrackHelper;
use Give\Tracking\Repositories\TrackEvents;

/**
 * Class TrackJob
 *
 * @package Give\Tracking
 * @since 2.10.0
 */
class TrackJob {
	/**
	 * @var TrackClient
	 */
	private $trackClient;

	/**
	 * @var TrackEvents
	 */
	private $trackEvents;

	/**
	 * TrackJob constructor.
	 *
	 * @param  TrackClient  $trackClient
	 * @param  TrackEvents  $trackEvents
	 */
	public function __construct( TrackClient $trackClient, TrackEvents $trackEvents ) {
		$this->trackClient = $trackClient;
		$this->trackEvents = $trackEvents;
	}

	/**
	 * Send tracks.
	 *
	 * @since 2.10.0
	 */
	public function send() {
		$recordedTracks = $this->trackEvents->getTrackList();

		if ( ! $recordedTracks || ! TrackHelper::isTrackingEnabled() ) {
			return;
		}

		foreach ( $recordedTracks as $trackId => $className ) {
			/* @var TrackData $class */
			$class     = give( $className );
			$eventType = new EventType( $trackId );

			if ( $class instanceof TrackData ) {
				$this->trackClient->post( $eventType, $class );
			}
		}

		$this->trackEvents->saveRequestTime();
		$this->trackEvents->removeTrackList();
	}
}
