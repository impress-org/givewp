<?php

namespace Give\MigrationLog;

use Give\ServiceProviders\ServiceProvider;
use Give\Framework\Migrations\MigrationsRegister;
use Give\MigrationLog\Migrations\CreateMigrationsTable;

/**
 * Class LogServiceProvider
 * @package Give\Log
 *
 * @since 2.9.7
 */
class MigrationLogServiceProvider implements ServiceProvider {
	/**
	 * @inheritdoc
	 */
	public function register() {
		global $wpdb;

		$wpdb->give_log = "{$wpdb->prefix}give_migration";

		give()->singleton( MigrationLogRepository::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		give( MigrationsRegister::class )->addMigration( CreateMigrationsTable::class );
	}
}
