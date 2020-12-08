<?php
namespace Give\Framework\Migrations;

use Give\Framework\Migrations\Controllers\ManualMigration;
use Give\Framework\Migrations\MigrationsRunner;
use Give\ServiceProviders\ServiceProvider;
use Give\Helpers\Hooks;

/**
 * Class DatabaseServiceProvider
 * @package Give\Framework\Migrations
 *
 * @since 2.9.0
 */
class MigrationsServiceProvider implements ServiceProvider {
	/**
	 * @inheritdoc
	 */
	public function register() {
		give()->singleton( MigrationsRunner::class );
		give()->singleton( MigrationsRegister::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		Hooks::addAction( 'admin_init', ManualMigration::class, '__invoke', 0 );
		Hooks::addAction( 'admin_init', MigrationsRunner::class, 'run', 0 );
		Hooks::addAction( 'give_upgrades', MigrationsRunner::class, 'run', 0 );
	}
}
