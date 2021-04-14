<?php

namespace Give\Tracking;

use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Enum\EventType;
use Give\Tracking\Events\EditedDonationFormsTracking;
use Give\Tracking\Helpers\Track as TrackHelper;
use Give\Tracking\Repositories\TrackEvents;
use Give\Tracking\TrackingData\EditedDonationFormsData;

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

		if ( ! $recordedTracks ) {
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

		if ( in_array( EditedDonationFormsData::class, $recordedTracks, true ) ) {
			$this->trackEvents->removeRecentlyEditedDonationFormList();
		}
	}

	/**
	 * Send tracked information immediately.
	 *
	 * @since 2.10.2
	 * @param array $trackedEvents
	 */
	public function sendNow( $trackedEvents ) {
		/* @var TrackEvents $trackEvents */
		$trackEvents = give( TrackEvents::class );

		foreach ( $trackedEvents as $trackEvent ) {
			give( $trackEvent )->record();
		}

		$trackEvents->saveTrackList();
		$this->send();

		// Do not setup cron job.
		$class = TrackJobScheduler::class;
		add_filter( "give_disable_hook-shutdown:{$class}@schedule", '__return_true' );
	}
}
