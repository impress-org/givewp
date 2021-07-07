<?php

declare( strict_types=1 );

namespace Give\License;

use Give\ServiceProviders\ServiceProvider;

class LicenseServiceProvider implements ServiceProvider {
	/**
	 * @unreleased
	 */
	public function register() {
		give()->singleton( PremiumAddonsListManager::class );
	}

	/**
	 * @unreleased
	 */
	public function boot() {
	}
}
