<?php

namespace Give\Log;

use Give\ServiceProviders\ServiceProvider;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Log\Migrations\CreateNewLogTables;
use Give\Log\Migrations\MigrateExistingLogs;
use Give\Log\Migrations\DeleteOldLogTables;

/**
 * Class LogServiceProvider
 * @package Give\Log
 *
 * @since 2.9.7
 */
class LogServiceProvider implements ServiceProvider {
	/**
	 * @inheritdoc
	 */
	public function register() {
		global $wpdb;

		$wpdb->give_logs    = "{$wpdb->prefix}give_logs_v2";
		$wpdb->give_logmeta = "{$wpdb->prefix}give_logmeta_v2";

		give()->singleton( LogRepository::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		give( MigrationsRegister::class )->addMigrations(
			[
				CreateNewLogTables::class,
				MigrateExistingLogs::class,
				DeleteOldLogTables::class,
			]
		);
	}
}
