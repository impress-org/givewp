<?php

namespace Give\Tracking\Repositories;

use Give\Tracking\TrackingData\WebsiteInfoData;
use Give\Tracking\TrackRegisterer;

/**
 * Class EventRecord
 * @package Give\Tracking\Repositories
 *
 * @since 2.10.0
 */
class TrackEvents {
	const TELEMETRY_REQUEST_TIME_OPTION_KEY        = 'give_telemetry_usage_tracking_last_request';
	const TRACKING_EVENTS_RECORD_OPTION_KEY        = 'give_telemetry_records';
	const RECENTLY_EDITED_DONATION_FORM_OPTION_KEY = 'give_telemetry_recently_edited_donation_form';

	/**
	 * Remove tracks.
	 *
	 * @since 2.10.0
	 */
	public function removeTrackList() {
		delete_option( self::TRACKING_EVENTS_RECORD_OPTION_KEY );
	}

	/**
	 * Remove list of recently edited donaiton forms.
	 *
	 * @since 2.10.2
	 */
	public function removeRecentlyEditedDonationFormList() {
		delete_option( self::RECENTLY_EDITED_DONATION_FORM_OPTION_KEY );
	}

	/**
	 * Save tracks.
	 *
	 * @since 2.10.0
	 */
	public function saveTrackList() {
		/* @var TrackRegisterer $trackRegisterer */
		$trackRegisterer = give( TrackRegisterer::class );
		update_option( self::TRACKING_EVENTS_RECORD_OPTION_KEY, $trackRegisterer->getTrackList(), false );
	}

	/**
	 * Save recently edited donation form.
	 *
	 * @since 2.10.2
	 */
	public function saveRecentlyEditedDonationForm( $formId ) {
		$formIds   = $this->getRecentlyEditedDonationFormsList();
		$formIds[] = $formId;

		$formIds = array_unique( $formIds );
		update_option( self::RECENTLY_EDITED_DONATION_FORM_OPTION_KEY, $formIds, false );
	}

	/**
	 * Get recently edited donation form list.
	 *
	 * @since 2.10.2
	 */
	public function getRecentlyEditedDonationFormsList() {
		return get_option( self::RECENTLY_EDITED_DONATION_FORM_OPTION_KEY, [] );
	}

	/**
	 * Get tracks list.
	 *
	 * @since 2.10.0
	 */
	public function getTrackList() {
		return get_option( self::TRACKING_EVENTS_RECORD_OPTION_KEY, [] );
	}

	/**
	 * Save request time.
	 *
	 * @since 2.10.0
	 */
	public function saveRequestTime() {
		update_option( self::TELEMETRY_REQUEST_TIME_OPTION_KEY, strtotime( '- 1 hour', current_time( 'timestamp' ) ), false );
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
		return date( 'Y-m-d H:i:s', get_option( self::TELEMETRY_REQUEST_TIME_OPTION_KEY, $today ) );
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

		update_option( $optionName, $checksum, false );

		return true;
	}
}
