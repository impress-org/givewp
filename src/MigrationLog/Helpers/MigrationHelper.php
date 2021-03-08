<?php

namespace Give\MigrationLog\Helpers;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\MigrationsRegister;
use Give\MigrationLog\MigrationLogModel;
use Give\MigrationLog\MigrationLogRepository;

/**
 * Class MigrationOrder
 * @package Give\MigrationLog\Helpers
 *
 * Helper class used to get migration data
 *
 * @since 2.10.0
 */
class MigrationHelper {

	/**
	 * @var MigrationsRegister
	 */
	private $migrationRegister;

	/**
	 * @var MigrationLogRepository
	 */
	private $migrationRepository;

	/**
	 * @var MigrationLogModel[]
	 */
	private $migrationsInDatabase;

	/**
	 * MigrationOrder constructor.
	 *
	 * @param  MigrationsRegister  $migrationRegister
	 * @param  MigrationLogRepository  $migrationRepository
	 */
	public function __construct(
		MigrationsRegister $migrationRegister,
		MigrationLogRepository $migrationRepository
	) {
		$this->migrationRegister    = $migrationRegister;
		$this->migrationRepository  = $migrationRepository;
		$this->migrationsInDatabase = $this->migrationRepository->getMigrations();
	}

	/**
	 * Get migrations sorted by run order
	 *
	 * @return array
	 */
	private function getMigrationsSorted() {
		static $migrations = [];

		if ( empty( $migrations ) ) {
			/* @var Migration $migrationClass */
			foreach ( $this->migrationRegister->getMigrations() as $migrationClass ) {
				$migrations[ $migrationClass::timestamp() . '_' . $migrationClass::id() ] = $migrationClass::id();
			}

			ksort( $migrations );
		}

		return $migrations;
	}

	/**
	 * Get pending migrations
	 *
	 * @return string[]
	 */
	public function getPendingMigrations() {
		return array_filter(
			$this->migrationRegister->getMigrations(),
			function( $migrationClass ) {
				/* @var Migration $migrationClass */
				foreach ( $this->migrationsInDatabase as $migration ) {
					if ( $migration->getId() === $migrationClass::id() ) {
						return false;
					}
				}
				return true;
			}
		);
	}

	/**
	 * Get migration run order
	 *
	 * @param string $migrationId
	 *
	 * @return int
	 */
	public function getRunOrderForMigration( $migrationId ) {
		return array_search( $migrationId, array_values( $this->getMigrationsSorted() ) ) + 1;
	}
}
