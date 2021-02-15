<?php
namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\PluginsData;
use Give\Tracking\ValueObjects\EventType;

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
	protected $trackId;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 * @param  PluginsData  $pluginData
	 */
	public function __construct( Track $track, PluginsData $pluginData ) {
		$this->trackId = ( new EventType() )->getPluginListUpdated();
		parent::__construct( $track, $pluginData );
	}
}
