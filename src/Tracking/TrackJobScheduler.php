<?php

namespace Give\Tracking;

use Give\Tracking\Repositories\EventRecord;

/**
 * Class TrackJobScheduler
 * @package Give\Tracking
 * @since 2.10.0
 */
class TrackJobScheduler {
	/**
	 * Cron job name.
	 * @var string
	 */
	const CRON_JOB_NAME = 'give_telemetry_send_requests';

	/**
	 * @var TrackRegisterer
	 */
	private $track;

	/**
	 * @var EventRecord
	 */
	private $eventRecord;

	/**
	 * TrackJobScheduler constructor.
	 *
	 * @param  TrackRegisterer  $track
	 * @param  EventRecord  $eventRecord
	 */
	public function __construct( TrackRegisterer $track, EventRecord $eventRecord ) {
		$this->track       = $track;
		$this->eventRecord = $eventRecord;
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

		$this->eventRecord->saveTrackList();
		if ( ! wp_next_scheduled( self::CRON_JOB_NAME ) ) {
			wp_schedule_single_event( strtotime( 'tomorrow midnight', current_time( 'timestamp' ) ), self::CRON_JOB_NAME );
		}
	}
}
