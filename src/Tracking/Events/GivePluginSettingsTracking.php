<?php
namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\TrackRegisterer;
use Give\Tracking\TrackingData\GivePluginSettingsData;
use Give\Tracking\Enum\EventType;

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
	protected $dataClassName = GivePluginSettingsData::class;

	/**
	 * GivePluginSettingsTracking constructor.
	 *
	 * @param  TrackRegisterer  $track
	 */
	public function __construct( TrackRegisterer $track ) {
		$this->eventType = new EventType( EventType::PLUGIN_SETTINGS_UPDATED );
		parent::__construct( $track );
	}
}
