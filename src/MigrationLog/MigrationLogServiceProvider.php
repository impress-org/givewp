<?php

namespace Give\MigrationLog;

use Give\Helpers\Hooks;
use Give\MigrationLog\Helpers\Assets;
use Give\MigrationLog\Helpers\Environment;
use Give\ServiceProviders\ServiceProvider;
use Give\Framework\Migrations\MigrationsRegister;
use Give\MigrationLog\Migrations\CreateMigrationsTable;
use Give\MigrationLog\Migrations\MigrateCompletedMigrations;

/**
 * Class MigrationLogServiceProvider
 * @package Give\MigrationLog
 *
 * @since 2.10.0
 */
class MigrationLogServiceProvider implements ServiceProvider {
	/**
	 * @inheritdoc
	 */
	public function register() {
		global $wpdb;

		$wpdb->give_migrations = "{$wpdb->prefix}give_migrations";

		give()->singleton( MigrationLogRepository::class );
		give()->singleton( MigrationLogFactory::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		give( MigrationsRegister::class )->addMigrations(
			[
				CreateMigrationsTable::class,
				MigrateCompletedMigrations::class,
			]
		);

		// Hook up
		if ( Environment::isMigrationsPage() ) {
			Hooks::addAction( 'admin_enqueue_scripts', Assets::class, 'enqueueScripts' );
		}
	}
}
