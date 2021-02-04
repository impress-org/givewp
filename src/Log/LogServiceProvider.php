<?php

namespace Give\Log;

use Give\Helpers\Hooks;
use Give\Log\Admin\Page as AdminPage;
use Give\ServiceProviders\ServiceProvider;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Log\Migrations\CreateNewLogTable;
use Give\Log\Migrations\MigrateExistingLogs;
use Give\Log\Migrations\DeleteOldLogTables;
use Give\Log\Admin\Assets;

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

		$wpdb->give_log = "{$wpdb->prefix}give_log";

		give()->singleton( LogRepository::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		$this->registerMigrations();
		// todo: remove, just for testing
		Hooks::addAction( 'admin_menu', AdminPage::class, 'register' );
		Hooks::addAction( 'admin_enqueue_scripts', Assets::class, 'enqueueScripts' );
	}

	/**
	 * Register migration
	 */
	private function registerMigrations() {
		give( MigrationsRegister::class )->addMigrations(
			[
				CreateNewLogTable::class,
				MigrateExistingLogs::class,
				DeleteOldLogTables::class,
			]
		);
	}
}
