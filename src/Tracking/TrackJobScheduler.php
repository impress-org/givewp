<?php

namespace Give\Tracking;

use Give\Tracking\Repositories\TrackEvents;

/**
 * Class TrackJobScheduler
 * @package Give\Tracking
 * @since 2.10.0
 */
class TrackJobScheduler {
	const CRON_JOB_HOOK_NAME = 'give_telemetry_send_requests';

	/**
	 * @var TrackRegisterer
	 */
	private $track;

	/**
	 * @var TrackEvents
	 */
	private $trackEvents;

	/**
	 * TrackJobScheduler constructor.
	 *
	 * @param  TrackRegisterer  $track
	 * @param  TrackEvents  $trackEvents
	 */
	public function __construct( TrackRegisterer $track, TrackEvents $trackEvents ) {
		$this->track       = $track;
		$this->trackEvents = $trackEvents;
	}

	/**
	 * Schedule cron job to send request to telemetry server.
	 *
	 * @since 2.10.0
	 */
	public function schedule() {
		if ( ! $this->track->hasNewTracks() ) {
			return;
		}

		$hookName = self::CRON_JOB_HOOK_NAME;
		$this->trackEvents->saveTrackList();
		if ( ! wp_next_scheduled( $hookName ) ) {
			wp_schedule_single_event( strtotime( '+24 hours', current_time( 'timestamp' ) ), $hookName );
		}
	}
}
