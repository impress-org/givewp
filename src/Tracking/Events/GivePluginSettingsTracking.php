<?php
namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\GivePluginSettingsData;

/**
 * Class GivePluginSettingsTracking
 *
 * This class setup event to send tracked data request when Give plugin settings update.
 *
 * @since 2.10.0
 * @package Give\Tracking\Events
 */
class GivePluginSettingsTracking extends TrackEvent {
	/**
	 * @var string
	 */
	protected $trackId = 'give_plugin_settings_updated';

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 * @param  GivePluginSettingsData  $givePluginSettingsData
	 */
	public function __construct( Track $track, GivePluginSettingsData $givePluginSettingsData ) {
		parent::__construct( $track, $givePluginSettingsData );
	}
}
