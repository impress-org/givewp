<?php
namespace Give\Tracking\Events;

use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Track;
use Give\Tracking\TrackingData\GivePluginSettingsData;

/**
 * Class GivePluginSettingsTracking
 *
 * @since 2.10.0
 * @package Give\Tracking\Events
 */
class GivePluginSettingsTracking implements TrackEvent {
	/**
	 * @inheritdoc
	 */
	public function boot() {
		add_action( 'update_option_give_settings', [ $this, 'record' ] );
	}

	/**
	 * Send track
	 *
	 * @since 2.10.0
	 */
	public function record() {
		/* @var Track $track */
		$track = give( Track::class );
		/* @var GivePluginSettingsData $trackData */
		$trackData = give( GivePluginSettingsData::class );

		$track->recordTrack( 'givewp_plugin_settings_updated', $trackData->get() );
	}
}
