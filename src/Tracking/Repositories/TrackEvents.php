<?php

namespace Give\Tracking\Repositories;

use Give\Tracking\TrackingData\WebsiteInfoData;
use Give\Tracking\TrackRegisterer;

use function GuzzleHttp\Psr7\str;

/**
 * Class EventRecord
 * @package Give\Tracking\Repositories
 *
 * @since 2.10.0
 */
class TrackEvents {
	/**
	 * Get option key for usage tracking last request.
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	public function getTelemetryRequestTimeOptionKey() {
		return 'give_telemetry_usage_tracking_last_request';
	}

	/**
	 * Get option key for tracking events record.
	 *
	 * @since 2.10.0
	 *
	 * @return string
	 */
	public function getTrackingEventsRecordOptionKey() {
		return 'give_telemetry_records';
	}

	/**
	 * Remove tracks.
	 *
	 * @since 2.10.0
	 */
	public function remove() {
		delete_option( $this->getTrackingEventsRecordOptionKey() );
	}

	/**
	 * Save tracks.
	 *
	 * @since 2.10.0
	 */
	public function saveTrackList() {
		/* @var TrackRegisterer $trackRegisterer */
		$trackRegisterer = give( TrackRegisterer::class );
		update_option( $this->getTrackingEventsRecordOptionKey(), $trackRegisterer->getTrackList() );
	}

	/**
	 * Get tracks list.
	 *
	 * @since 2.10.0
	 */
	public function getTrackList() {
		return get_option( $this->getTrackingEventsRecordOptionKey(), [] );
	}

	/**
	 * Save request time.
	 *
	 * @since 2.10.0
	 */
	public function saveRequestTime() {
		update_option( $this->getTelemetryRequestTimeOptionKey(), strtotime( 'today', current_time( 'timestamp' ) ) );
	}

	/**
	 * Get request time.
	 *
	 * @since 2.10.0
	 *
	 * @return false|string
	 */
	public function getRequestTime() {
		$today = strtotime( 'today', current_time( 'timestamp' ) );
		return date( 'Y-m-d H:i:s', get_option( $this->getTelemetryRequestTimeOptionKey(), $today ) );
	}

	/**
	 * Store website tracking event.
	 *
	 * @since 2.10.0
	 */
	public function storeWebsiteTrackingEvent() {
		/* @var WebsiteInfoData $dataClass */
		$dataClass        = give( WebsiteInfoData::class );
		$optionName       = 'give_telemetry_website_data_checksum';
		$previousChecksum = get_option( $optionName, '' );
		$checksum         = substr( md5( serialize( $dataClass->get() ) ), 0, 32 );

		if ( $previousChecksum === $checksum ) {
			return false;
		}

		update_option( $optionName, $checksum );

		return true;
	}
}
