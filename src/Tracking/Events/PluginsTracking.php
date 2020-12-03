<?php
namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\PluginsData;

/**
 * Class PluginsTracking
 *
 * This class setup event to send tracked data request when active plugin list update.
 *
 * @since 2.10.0
 * @package Give\Tracking\Events
 */
class PluginsTracking extends TrackEvent {
	/**
	 * @var string
	 */
	protected $trackId = 'plugin_list_updated';

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 * @param  PluginsData  $pluginData
	 */
	public function __construct( Track $track, PluginsData $pluginData ) {
		parent::__construct( $track, $pluginData );
	}
}
