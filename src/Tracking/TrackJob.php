<?php

namespace Give\Tracking;

use Braintree\Exception;
use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Enum\EventType;
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
	 * @var TrackRegisterer
	 */
	private $track;

	/**
	 * @var TrackClient
	 */
	private $trackClient;

	/**
	 * TrackJob constructor.
	 *
	 * @param  TrackRegisterer  $track
	 * @param  TrackClient  $trackClient
	 */
	public function __construct( TrackRegisterer $track, TrackClient $trackClient ) {
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
			$class     = give( $className );
			$eventType = new EventType( $trackId );

			if ( $class instanceof TrackData ) {
				$this->trackClient->post( $eventType, $class );
			}
		}

		update_option( self::LAST_REQUEST_OPTION_NAME, strtotime( 'today', current_time( 'timestamp' ) ) );
		$this->track->remove();
	}
}
