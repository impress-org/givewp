<?php
namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\PluginsData;
use Give\Tracking\Enum\EventType;

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
	 * @var string
	 */
	protected $dataClassName = PluginsData::class;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  Track  $track
	 */
	public function __construct( Track $track ) {
		$this->trackId = new EventType( 'plugin-list-updated' );
		parent::__construct( $track );
	}
}
