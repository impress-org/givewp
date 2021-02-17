<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Repositories\EventRecord;
use Give\Tracking\TrackingData\WebsiteInfoData;
use Give\Tracking\TrackRegisterer;
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
	protected $dataClassName = WebsiteInfoData::class;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @since 2.10.0
	 *
	 * @param  TrackRegisterer  $track
	 *
	 */
	public function __construct( TrackRegisterer $track ) {
		$this->eventType = new EventType( 'site-updated' );
		parent::__construct( $track );
	}

	/**
	 * Website data tracking handler.
	 *
	 * @since 2.10.0
	 */
	public function websiteUpdateTrackingHandler() {
		if ( EventRecord::storeWebsiteTrackingEvent() ) {
			$this->record();
		}
	}
}
