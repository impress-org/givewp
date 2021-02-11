<?php

namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackData;
use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\ServerData;
use Give\Tracking\TrackingData\ThemeData;
use Give\Tracking\TrackingData\WebsiteData;

/**
 * Class WebsiteTracking
 * @package Give\Tracking\Events
 *
 * @since 2.10.0
 */
class WebsiteTracking extends TrackEvent {
	protected $trackId = 'site-updated';

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  WebsiteData  $themeData
	 * @param  Track  $track
	 *
	 * @since 2.10.0
	 */
	public function __construct( Track $track, WebsiteData $themeData ) {
		parent::__construct( $track, $themeData );
	}
}
