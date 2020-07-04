<?php

namespace Give\ServiceProviders;

use Give\Database\Migrations\CreateWebhookEventsTable;
use Give\Database\Migrations\DatabaseMigration;
use Give_Updates;

class DatabaseMigrations implements ServiceProvider {
	/**
	 * Array of the DatabaseMigration classes
	 *
	 * @var string[]
	 */
	private $migrations = [
		CreateWebhookEventsTable::class,
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_action( 'give_register_updates', [ $this, 'registerMigrations' ] );
	}

	/**
	 * Registers all of the database migrations
	 *
	 * @since 2.8.0
	 *
	 * @param Give_Updates $giveUpdates
	 */
	public function registerMigrations( Give_Updates $giveUpdates ) {
		foreach ( $this->migrations as $migration ) {
			/** @var DatabaseMigration $migration */
			$migration = new $migration();

			$giveUpdates->register(
				[
					'id'       => $migration->getId(),
					'version'  => $migration->getVersion(),
					'callback' => [ $migration, 'runMigration' ],
				]
			);
		}
	}
}
