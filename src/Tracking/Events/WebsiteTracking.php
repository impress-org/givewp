<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\WebsiteData;
use Give\Tracking\Enum\EventType;

/**
 * Class WebsiteTracking
 * @package Give\Tracking\Events
 *
 * @since 2.10.0
 */
class WebsiteTracking extends TrackEvent {
	/**
	 * @var string
	 */
	protected $trackId;

	/**
	 * @var string
	 */
	protected $dataClassName = WebsiteData::class;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 *
	 * @since 2.10.0
	 */
	public function __construct( Track $track ) {
		$this->trackId = new EventType( 'site-updated' );
		parent::__construct( $track );
	}

	/**
	 * Website data tracking handler.
	 *
	 * @since 2.10.0
	 */
	public function websiteUpdateTrackingHandler() {
		/* @var WebsiteData $dataClass */
		$dataClass        = give( $this->dataClassName );
		$optionName       = 'give_telemetry_website_data_checksum';
		$previousChecksum = get_option( $optionName, '' );
		$checksum         = substr( md5( serialize( $dataClass->get() ) ), 0, 32 );

		if ( $previousChecksum === $checksum ) {
			return;
		}

		update_option( $optionName, $checksum );
		$this->record();
	}
}
