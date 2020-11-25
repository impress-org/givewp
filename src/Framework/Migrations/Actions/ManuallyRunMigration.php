<?php

namespace Give\Framework\Migrations\Actions;

use Exception;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\MigrationsRunner;

class ManuallyRunMigration {
	/**
	 * Manually runs the migration and then marks the migration as finished if successful
	 *
	 * @since 2.9.2
	 *
	 * @param Migration $migration
	 *
	 */
	public function __invoke( Migration $migration ) {
		global $wpdb;

		$wpdb->query( 'START TRANSACTION' );

		try {
			$migration->run();
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK' );

			give_record_log( 'Migration Failed', print_r( $exception, true ), 0, 'update' );
			give()->notices->register_notice(
				[
					'id'          => 'migration-failure',
					'description' => sprintf(
						'%1$s <a href="https://givewp.com/support/">https://givewp.com/support</a>',
						esc_html__( 'There was a problem running the migrations. Please reach out to GiveWP support for assistance:', 'give' )
					),
				]
			);

			throw $exception;
		}

		// Commit transaction if successful
		$wpdb->query( 'COMMIT' );

		$this->updateMigrationsSetting( $migration::id() );
	}

	/**
	 * Updates the completed migrations to include the migration if not yet included
	 *
	 * @since 2.9.2
	 *
	 * @param string $migrationId
	 */
	private function updateMigrationsSetting( $migrationId ) {
		$completedMigrations = get_option( MigrationsRunner::MIGRATION_OPTION );

		if ( in_array( $migrationId, $completedMigrations, true ) ) {
			return;
		}

		$completedMigrations[] = $migrationId;

		update_option( MigrationsRunner::MIGRATION_OPTION, $completedMigrations );
	}
}
