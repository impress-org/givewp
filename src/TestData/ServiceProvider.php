<?php

namespace Give\TestData;

use WP_CLI;
use Give\Helpers\Hooks;

class ServiceProvider implements \Give\ServiceProviders\ServiceProvider {

	/**
	 * @inheritDoc
	 *
	 * @since UNRELEASED
	 */
	public function register() {
		// ...
	}

	/**
	 * @inheritDoc
	 *
	 * @since UNRELEASED
	 */
	public function boot() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'give test-data', give()->make( SeedCommand::class ) );
		}
	}
}
