<?php

namespace Give\Tracking\Repositories;

use Give\Tracking\TrackingData\WebsiteInfoData;
use Give\Tracking\TrackRegisterer;

/**
 * Class EventRecord
 * @package Give\Tracking\Repositories
 */
class EventRecord {
	/**
	 * Option name to store last request time.
	 */
	const LAST_REQUEST_OPTION_NAME = 'give_usage_tracking_last_request';

	/**
	 * Option name to record tracks.
	 */
	const TRACK_RECORDS_OPTION_NAME = 'give_telemetry_records';

	/**
	 * @var TrackRegisterer
	 */
	private $trackRegisterer;

	/**
	 * EventRecord constructor.
	 *
	 * @param  TrackRegisterer  $trackRegisterer
	 *
	 * @since 2.10.0
	 */
	public function __construct( TrackRegisterer $trackRegisterer ) {
		$this->trackRegisterer = $trackRegisterer;
	}

	/**
	 * Remove tracks.
	 *
	 * @since 2.10.0
	 */
	public static function remove() {
		delete_option( self::TRACK_RECORDS_OPTION_NAME );
	}

	/**
	 * Save tracks.
	 *
	 * @since 2.10.0
	 */
	public function saveTrackList() {
		update_option( self::TRACK_RECORDS_OPTION_NAME, $this->trackRegisterer->getTrackList() );
	}

	/**
	 * Get tracks list.
	 *
	 * @since 2.10.0
	 */
	public static function getTrackList() {
		return get_option( self::TRACK_RECORDS_OPTION_NAME, [] );
	}

	/**
	 * Save request time.
	 *
	 * @since 2.10.0
	 */
	public static function saveRequestTime() {
		update_option( self::LAST_REQUEST_OPTION_NAME, strtotime( 'today', current_time( 'timestamp' ) ) );
	}

	/**
	 * Get request time.
	 *
	 * @since 2.10.0
	 */
	public static function getRequestTimeWithDefault() {
		$defaultTime = strtotime( 'today', current_time( 'timestamp' ) );

		return date( 'Y-m-d H:i:s', get_option( self::LAST_REQUEST_OPTION_NAME, $defaultTime ) );
	}

	/**
	 * Store website tracking event.
	 *
	 * @since 2.10.0
	 */
	public static function storeWebsiteTrackingEvent() {
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
