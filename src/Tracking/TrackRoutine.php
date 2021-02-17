<?php

namespace Give\Tracking;

/**
 * Class TrackRoutine
 *
 * @since 2.10.0
 * @package Give\Tracking
 */
class TrackRoutine {
	const LAST_REQUEST_OPTION_NAME = 'give_usage_tracking_last_request';

	/**
	 * The limit for the option.
	 *
	 * @var int
	 */
	protected $threshold = WEEK_IN_SECONDS * 2;

	/**
	 * Sends the tracking data.
	 *
	 * @since 2.10.0
	 *
	 * @param  bool  $force  Whether to send the tracking data ignoring the two
	 *                    weeks time threshold. Default false.
	 */
	public function send( $force = false ) {
		if ( ! $this->shouldSendTracking( $force ) ) {
			return;
		}

		update_option( self::LAST_REQUEST_OPTION_NAME, time() );

		/**
		 * Fire action to send routine tracking events.
		 *
		 * @since 2.10.0
		 */
		do_action( 'give_send_tracking_data' );
	}

	/**
	 * Determines whether to send the tracking data.
	 *
	 * @since 2.10.0
	 *
	 * @param  bool  $ignore_time_threshold  Whether to send the tracking data ignoring the two weeks time threshold. Default false.
	 *
	 * @return bool True when tracking data should be sent.
	 */
	private function shouldSendTracking( $ignore_time_threshold = false ) {
		// Only send tracking on the main site of a multi-site instance. This returns true on non-multisite installs.
		if ( ! is_main_site() ) {
			return false;
		}

		$lastTime = get_option( self::LAST_REQUEST_OPTION_NAME, 0 );

		// When tracking data haven't been sent yet or when sending data is forced.
		if ( ! $lastTime || $ignore_time_threshold ) {
			return true;
		}

		return $this->exceedsThreshold( time() - $lastTime );
	}

	/**
	 * Checks if the given amount of seconds exceeds the set threshold.
	 *
	 * @since 2.10.0
	 *
	 * @param  int  $seconds  The amount of seconds to check.
	 *
	 * @return bool True when seconds is bigger than threshold.
	 */
	private function exceedsThreshold( $seconds ) {
		return ( $seconds > $this->threshold );
	}
}
