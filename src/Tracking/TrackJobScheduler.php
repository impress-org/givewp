<?php

namespace Give\Tracking;

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
	 * @var Track
	 */
	private $track;

	/**
	 * TrackJobScheduler constructor.
	 *
	 * @param  Track  $track
	 */
	public function __construct( Track $track ) {
		$this->track = $track;
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

		$this->track->save();

		if ( ! wp_next_scheduled( self::CRON_JOB_NAME ) ) {
			wp_schedule_single_event( strtotime( 'tomorrow - 1 day', current_time( 'timestamp' ) ), self::CRON_JOB_NAME );
		}
	}
}
