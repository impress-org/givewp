<?php

namespace Give\Framework\Migrations\Actions;

use Give\Framework\Migrations\MigrationsRegister;

class ManuallyRunMigration {
	/**
	 * @var MigrationsRegister
	 */
	private $migrationsRegister;

	/**
	 * ManualMigration constructor.
	 *
	 * @since 2.9.2
	 */
	public function __construct( MigrationsRegister $migrationsRegister ) {
		$this->migrationsRegister = $migrationsRegister;
	}

	public function __invoke( $migrationId ) {
		$migrationClass = $this->migrationsRegister->getMigration( $migrationId );

	}
}
