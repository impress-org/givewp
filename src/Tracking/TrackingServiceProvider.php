<?php
namespace Give\Tracking;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider;

/**
 * Class TrackingServiceProvider
 * @package Give\Tracking
 *
 * @since 2.10.0
 */
class TrackingServiceProvider implements ServiceProvider {
	/**
	 * @inheritdoc
	 */
	public function register() {
		give()->singleton( AdminSettings::class );
		give()->singleton( UsageTrackingOnBoarding::class );
		give()->singleton( AdminActionHandler::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		if ( is_admin() ) {
			give( AdminSettings::class )->boot();
			give( UsageTrackingOnBoarding::class )->boot();
			give( AdminActionHandler::class )->boot();
		}
	}
}
