<?php

namespace Give\Framework\Migrations\Controllers;

use Give\Framework\Migrations\MigrationsRegister;
use Give_Admin_Settings;

/**
 * Class ManualMigration
 *
 * Handles and admin request to manually trigger migrations
 *
 * @since 2.9.2
 */
class ManualMigration {
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

	/**
	 * @since 2.9.2
	 */
	public function __invoke() {
		if ( ! empty( $_GET['give-run-migration'] ) ) {
			$migrationToRun = $_GET['give-run-migration'];
		}

		if ( ! empty( $_GET['give-clear-migration'] ) ) {
			$migrationToClear = $_GET['give-clear-migration'];
		}

		if ( ( isset( $migrationToRun ) || isset( $migrationToClear ) ) && current_user_can( 'manage_options' ) ) {
			Give_Admin_Settings::add_error(
				'invalid-migration-permissions',
				__( 'You do not have the permissions to manually run or clear migrations', 'give' )
			);

			return;
		}

		if ( isset( $migrationToRun ) ) {
			if ( ! $this->migrationsRegister->hasMigration( $migrationToRun ) ) {
				Give_Admin_Settings::add_error(
					'invalid-migration-id',
					__( "There is no migration with the ID: $migrationToRun", 'give' )
				);
				return;
			}
		}
	}
}
