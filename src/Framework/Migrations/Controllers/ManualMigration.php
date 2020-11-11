<?php

namespace Give\Framework\Migrations\Controllers;

use Exception;
use Give\Framework\Migrations\Actions\ClearCompletedUpgrade;
use Give\Framework\Migrations\Actions\ManuallyRunMigration;
use Give\Framework\Migrations\Contracts\Migration;
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

		if ( ! empty( $_GET['give-clear-update'] ) ) {
			$migrationToClear = $_GET['give-clear-update'];
		}

		$hasMigration = isset( $migrationToRun ) || isset( $migrationToClear );

		if ( $hasMigration && ! current_user_can( 'manage_options' ) ) {
			Give_Admin_Settings::add_error(
				'invalid-migration-permissions',
				__( 'You do not have the permissions to manually run or clear migrations', 'give' )
			);

			return;
		}

		if ( isset( $migrationToRun ) ) {
			$this->runMigration( $migrationToRun );
		}

		if ( isset( $migrationToClear ) ) {
			$this->clearMigration( $migrationToClear );
		}

		if ( $hasMigration ) {
			$uriDetails = parse_url( $_SERVER['REQUEST_URI'] );
			parse_str( $uriDetails['query'], $queryData );

			unset( $queryData['give-run-migration'], $queryData['give-clear-update'] );

			wp_safe_redirect( $uriDetails['path'] . '?' . http_build_query( $queryData ) );
		}
	}

	/**
	 * Runs the given automatic migration
	 *
	 * @since 2.9.2
	 *
	 * @param string $migrationId
	 */
	private function runMigration( $migrationId ) {
		if ( ! $this->migrationsRegister->hasMigration( $migrationId ) ) {
			Give_Admin_Settings::add_error(
				'invalid-migration-id',
				__( "There is no migration with the ID: $migrationId", 'give' )
			);

			return;
		}

		/** @var Migration $migration */
		$migration = give( $this->migrationsRegister->getMigration( $migrationId ) );

		/** @var ManuallyRunMigration $manualRunner */
		$manualRunner = give( ManuallyRunMigration::class );

		$manualRunner( $migration );

		Give_Admin_Settings::add_message( 'automatic-migration-run', "The $migrationId migration was manually triggered" );
	}

	/**
	 * Clears the manual migration so it may be run again
	 *
	 * @since 2.9.2
	 *
	 * @param string $migrationToClear
	 */
	private function clearMigration( $migrationToClear ) {
		/** @var ClearCompletedUpgrade $clearUpgrade */
		$clearUpgrade = give( ClearCompletedUpgrade::class );

		try {
			$clearUpgrade( $migrationToClear );
		} catch ( Exception $exception ) {
			Give_Admin_Settings::add_error( 'clear-migration-failed', "Unable to reset migration. Error: {$exception->getMessage()}" );
		}

		Give_Admin_Settings::add_message( 'automatic-migration-cleared', "The $migrationToClear update was cleared and may be run again." );
	}
}
