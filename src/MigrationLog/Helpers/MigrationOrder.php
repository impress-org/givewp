<?php

namespace Give\MigrationLog\Helpers;

use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\MigrationsRegister;

/**
 * Class MigrationOrder
 * @package Give\MigrationLog\Helpers
 *
 * Helper class used to get migration run order
 *
 * @since 2.10.0
 */
class MigrationOrder {

	/**
	 * @var MigrationsRegister
	 */
	private $migrationRegister;

	/**
	 * MigrationOrder constructor.
	 *
	 * @param  MigrationsRegister  $migrationRegister
	 */
	public function __construct( MigrationsRegister $migrationRegister ) {
		$this->migrationRegister = $migrationRegister;
	}

	/**
	 * Get migration run order
	 *
	 * @param string $migrationId
	 *
	 * @return int
	 */
	public function getRunOrderForMigration( $migrationId ) {
		static $migrations = [];

		if ( empty( $migrations ) ) {
			/* @var Migration $migrationClass */
			foreach ( $this->migrationRegister->getMigrations() as $migrationClass ) {
				$migrations[ $migrationClass::timestamp() . '_' . $migrationClass::id() ] = $migrationClass::id();
			}

			ksort( $migrations );
		}

		return array_search( $migrationId, array_values( $migrations ) ) + 1;
	}
}
