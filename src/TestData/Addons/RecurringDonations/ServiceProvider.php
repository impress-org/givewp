<?php

namespace Give\TestData\Addons\RecurringDonations;

use WP_CLI;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as GiveServiceProvider;

/**
 * Class ServiceProvider
 * @package Give\TestData\RecurringDonations
 */
class ServiceProvider implements GiveServiceProvider {
	/**
	 * @inheritDoc
	 */
	public function register() {
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'give test-recurring-demonstration-page', give()->make( PageSeedCommand::class ) );
		}
		// Update donation meta on donation insert
		Hooks::addAction( 'give-test-data-insert-donation', RecurringDonations::class, 'insertRecurringDonation', 10, 2 );
	}
}
