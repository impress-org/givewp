<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\WebsiteData;
use Give\Tracking\ValueObjects\EventType;

/**
 * Class WebsiteTracking
 * @package Give\Tracking\Events
 *
 * @since 2.10.0
 */
class WebsiteTracking extends TrackEvent {
	protected $trackId;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  WebsiteData  $themeData
	 * @param  Track  $track
	 *
	 * @since 2.10.0
	 */
	public function __construct( Track $track, WebsiteData $themeData ) {
		$this->trackId = ( new EventType() )->getSiteUpdated();
		parent::__construct( $track, $themeData );
	}
}
