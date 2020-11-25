<?php
namespace Give\Tracking;

use Give\ServiceProviders\ServiceProvider;
use Give\Tracking\Contracts\TrackEvent;
use Give\Tracking\Events\GivePluginSettingsTracking;
use Give\Tracking\Events\PluginsTracking;
use Give\Tracking\Events\ThemeTracking;

/**
 * Class TrackingServiceProvider
 * @package Give\Tracking
 *
 * @since 2.10.0
 */
class TrackingServiceProvider implements ServiceProvider {
	/**
	 * @var string[]
	 */
	private $trackEvents = [
		ThemeTracking::class,
		PluginsTracking::class,
		GivePluginSettingsTracking::class,
	];

	/**
	 * @inheritdoc
	 */
	public function register() {
		give()->singleton( AdminSettings::class );
		give()->singleton( AnonymousUsageTrackingOnBoarding::class );
		give()->singleton( AdminActionHandler::class );
		give()->singleton( Track::class );
		give()->singleton( TrackRoutine::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		give( Track::class )->boot();

		if ( is_admin() ) {
			give( AdminSettings::class )->boot();
			give( AnonymousUsageTrackingOnBoarding::class )->boot();
			give( AdminActionHandler::class )->boot();

			$this->registerTrackEvents();
		}

		give( TrackRoutine::class )->boot();
	}

	/**
	 * Register track events
	 *
	 * @since 2.10.0
	 */
	private function registerTrackEvents() {
		/* @var string $event Class name */
		/* @var TrackEvent $event */
		foreach ( $this->trackEvents as $event ) {
			$event = give( $event );
			$event->boot();
		}
	}
}
