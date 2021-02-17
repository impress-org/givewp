<?php

namespace Give\Tracking;

use Braintree\Exception;
use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Enum\EventType;
use Give\Tracking\Helpers\Track as TrackHelper;
use Give\Tracking\Repositories\EventRecord;

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
	 * TrackJob constructor.
	 *
	 * @param  TrackClient  $trackClient
	 */
	public function __construct( TrackClient $trackClient ) {
		$this->trackClient = $trackClient;
	}

	/**
	 * Send tracks.
	 *
	 * @since 2.10.0
	 */
	public function send() {
		$recordedTracks = EventRecord::getTrackList();

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

		EventRecord::saveRequestTime();
		EventRecord::remove();
	}
}
