<?php
namespace Give\Tracking;

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
		give()->singleton( HandleUsageTrackingRoutine::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		$isAdmin = is_admin();

		if ( $isAdmin || wp_doing_cron() ) {
			give( HandleUsageTrackingRoutine::class )->boot();
		}

		if ( $isAdmin ) {
			give( AdminSettings::class )->boot();
			give( UsageTrackingOnBoarding::class )->boot();
			give( AdminActionHandler::class )->boot();
		}
	}
}
