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
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		Hooks::addAction( 'admin_init', AdminSettings::class, 'register' );
	}
}
