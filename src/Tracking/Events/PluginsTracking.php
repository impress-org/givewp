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
class PluginsTracking implements TrackEvent {
	/**
	 * @inheritdoc
	 */
	public function boot() {
		add_action( 'update_option_active_plugins', [ $this, 'record' ] );
	}

	/**
	 * Send track
	 *
	 * @since 2.10.0
	 */
	public function record() {
		/* @var Track $track */
		$track = give( Track::class );
		/* @var PluginsData $trackData */
		$trackData = give( PluginsData::class );

		$track->recordTrack( 'plugin_list_updated', $trackData->get() );
	}
}
