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
		// Instead of passing around an instance, bind a singleton to the container.
		give()->singleton(
			\Faker\Generator::class,
			function() {
				return \Faker\Factory::create();
			}
		);
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
