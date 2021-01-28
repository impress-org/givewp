<?php

namespace Give\Log;

use Give\Log\ValueObjects\LogCategory;
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

		$wpdb->give_log = "{$wpdb->prefix}give_log";

		give()->singleton( LogRepository::class );
	}

	/**
	 * @inheritdoc
	 */
	public function boot() {
		give( MigrationsRegister::class )->addMigrations(
			[
				CreateNewLogTables::class,
			//              MigrateExistingLogs::class,
			//              DeleteOldLogTables::class,
			]
		);

		// Test it
		if ( CreateNewLogTables::check() ) {
			$this->testLogs();
		}

	}

	/**
	 * IGNORE - just for testing
	 */
	public function testLogs() {

		Log::success( 'Its working' );

		Log::error(
			'Hey man, this is an error!',
			[
				'category' => LogCategory::PAYMENT,
				'source'   => 'Stripe add-on',
			]
		);

		Log::migration( CreateNewLogTables::class )->success( 'Worked like a charm', self::class );

		Log::migration( CreateNewLogTables::class )->notice( 'Hmmm', self::class, [ 'info' => "Something isn't quite right" ] );
	}
}
