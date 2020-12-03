<?php
namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\ThemeData;

/**
 * Class ThemeTracking
 *
 * This class setup event to send tracked data request when active theme changes.
 *
 * @since 2.10.0
 * @package Give\Tracking\Admin\Events
 */
class ThemeTracking extends TrackEvent {
	/**
	 * @var string
	 */
	protected $trackId = 'theme_updated';

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 * @param  ThemeData  $themeData
	 */
	public function __construct( Track $track, ThemeData $themeData ) {
		parent::__construct( $track, $themeData );
	}
}
