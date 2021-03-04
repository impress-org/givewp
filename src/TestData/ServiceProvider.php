<?php

namespace Give\TestData;

use WP_CLI;
use InvalidArgumentException;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Give\TestData\Commands\DonorSeedCommand;
use Give\TestData\Commands\DonationSeedCommand;
use Give\TestData\Commands\DonationStatusCommand;
use Give\TestData\Commands\FormSeedCommand;
use Give\TestData\Commands\PageSeedCommand;
use Give\TestData\Commands\LogsSeedCommand;
use Give\ServiceProviders\ServiceProvider as GiveServiceProvider;

/**
 * Class ServiceProvider
 * @package Give\TestData
 */
class ServiceProvider implements GiveServiceProvider {
	/**
	 * @inheritDoc
	 */
	public function register() {
		// Instead of passing around an instance, bind a singleton to the container.
		give()->singleton(
			FakerGenerator::class,
			function () {
				return FakerFactory::create();
			}
		);
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		// Add CLI commands
		if ( defined( 'WP_CLI' ) && WP_CLI ) {

			$this->addCommands();

			try {
				$this->loadAddonsServiceProviders();
			} catch ( InvalidArgumentException $e ) {
				exit( $e->getMessage() );
			}
		}
	}

	/**
	 * Load addons service providers for TestData
	 */
	private function loadAddonsServiceProviders() {
		$providers = [];

		// Load Test Data add-ons Service Providers as they are not handled by Give
		foreach ( Addons::getActiveAddons() as $addon ) {
			if ( ! is_subclass_of( $addon['serviceProvider'], GiveServiceProvider::class ) ) {
				throw new InvalidArgumentException( "{$addon['serviceProvider']} class must implement the ServiceProvider interface" );
			}

			$addonServiceProvider = new $addon['serviceProvider']();
			$addonServiceProvider->register();
			$providers[] = $addonServiceProvider;
		}

		foreach ( $providers as $addonServiceProvider ) {
			$addonServiceProvider->boot();
		}
	}

	/**
	 * Add CLI comands
	 */
	private function addCommands() {
		WP_CLI::add_command( 'give test-donors', give()->make( DonorSeedCommand::class ) );
		WP_CLI::add_command( 'give test-donations', give()->make( DonationSeedCommand::class ) );
		WP_CLI::add_command( 'give test-donation-statuses', give()->make( DonationStatusCommand::class ) );
		WP_CLI::add_command( 'give test-demonstration-page', give()->make( PageSeedCommand::class ) );
		WP_CLI::add_command( 'give test-donation-form', give()->make( FormSeedCommand::class ) );
		WP_CLI::add_command( 'give test-logs', give()->make( LogsSeedCommand::class ) );
	}
}
