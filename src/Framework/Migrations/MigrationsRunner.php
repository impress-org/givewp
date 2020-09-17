<?php

namespace Give\Framework\Migrations;

use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class MigrationsRunner
 *
 * @since 2.9.0
 */
class MigrationsRunner {
	/**
	 * Option name to store competed migrations.
	 *
	 * @var string
	 */
	private $optionNameToStoreCompletedMigrations = 'give_database_migrations';

	/**
	 * List of completed migrations.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	private $completedMigrations;

	/**
	 * @since 2.9.0
	 *
	 * @var MigrationsRegister
	 */
	private $migrationRegister;

	/**
	 *  MigrationsRunner constructor.
	 *
	 * @param MigrationsRegister $migrationRegister
	 */
	public function __construct( MigrationsRegister $migrationRegister ) {
		$this->migrationRegister = $migrationRegister;

		$this->completedMigrations = get_option( $this->optionNameToStoreCompletedMigrations, [] );
	}

	/**
	 * Run database migrations.
	 *
	 * @since 2.9.0
	 */
	public function run() {
		if ( ! $this->hasMigrationToRun() ) {
			return;
		}

		// Store and sort migrations by timestamp
		$migrations = [];

		foreach ( $this->migrationRegister->getMigrations() as $migrationClass ) {
			/* @var Migration $migrationClass */
			$migrations[ $migrationClass::timestamp() . '_' . $migrationClass::id() ] = $migrationClass;
		}

		ksort( $migrations );

		// Process migrations.
		$newMigrations = [];

		foreach ( $migrations as $migrationClass ) {
			$migrationId = $migrationClass::id();

			if ( in_array( $migrationId, $this->completedMigrations, true ) ) {
				continue;
			}

			/** @var Migration $migration */
			$migration = give( $migrationClass );
			$migration->run();

			$newMigrations[] = $migrationId;
		}

		// Save processed migrations.
		$this->completedMigrations = array_unique( array_merge( $this->completedMigrations, $newMigrations ) );

		if ( $newMigrations ) {
			update_option(
				$this->optionNameToStoreCompletedMigrations,
				$this->completedMigrations
			);
		}
	}

	/**
	 * Return whether or not all migrations completed.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function hasMigrationToRun() {
		return (bool) array_diff( $this->migrationRegister->getRegisteredIds(), $this->completedMigrations );
	}
}
