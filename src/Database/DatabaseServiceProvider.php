<?php
namespace Give\Database;

use Give\ServiceProviders\ServiceProvider;
use Give\Helpers\Hooks;

/**
 * Class DatabaseServiceProvider
 * @package Give\Database
 *
 * @since 2.9.0
 */
class DatabaseServiceProvider implements ServiceProvider {

	/**
	 * @inheritdoc
	 */
	public function register() {
		give()->singleton( RunMigrations::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		Hooks::addAction( 'admin_init', RunMigrations::class, 'run', 0 );
		Hooks::addAction( 'give_upgrades', RunMigrations::class, 'run', 0 );
	}
}
