<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Repositories\TrackEvents;
use Give\Tracking\TrackingData\WebsiteInfoData;
use Give\Tracking\TrackRegisterer;
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
	 * @var TrackEvents
	 */
	private $trackEvents;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @since 2.10.0
	 *
	 * @param  TrackRegisterer  $track
	 * @param  TrackEvent  $trackEvents
	 */
	public function __construct( TrackRegisterer $track, TrackEvent $trackEvents ) {
		$this->eventType   = new EventType( 'site-updated' );
		$this->trackEvents = $trackEvents;

		parent::__construct( $track );
	}

	/**
	 * Website data tracking handler.
	 *
	 * @since 2.10.0
	 */
	public function websiteUpdateTrackingHandler() {
		if ( $this->trackEvents->storeWebsiteTrackingEvent() ) {
			$this->record();
		}
	}
}
