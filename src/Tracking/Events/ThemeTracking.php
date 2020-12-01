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
class ThemeTracking implements TrackEvent {

	/**
	 * @inheritdoc
	 */
	public function boot() {
		add_action( 'switch_theme', [ $this, 'record' ] );
	}

	/**
	 * Send track
	 *
	 * @since 2.10.0
	 */
	public function record() {
		/* @var Track $track */
		$track = give( Track::class );
		/* @var ThemeData $trackData */
		$trackData = give( ThemeData::class );

		$track->recordTrack( 'theme_updated', $trackData->get() );
	}
}
