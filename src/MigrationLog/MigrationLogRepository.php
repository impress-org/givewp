<?php

namespace Give\MigrationLog;

use Give\Framework\Database\DB;

/**
 * Class MigrationLogRepository
 * @package Give\MigrationLog
 *
 * @since 2.10.0
 */
class MigrationLogRepository {
	/**
	 * @var string
	 */
	private $migration_table;

	/**
	 * @var MigrationLogFactory
	 */
	private $migrationFactory;

	/**
	 * MigrationRepository constructor.
	 *
	 * @param  MigrationLogFactory  $migrationFactory
	 */
	public function __construct( MigrationLogFactory $migrationFactory ) {
		global $wpdb;
		$this->migration_table  = $wpdb->give_migrations;
		$this->migrationFactory = $migrationFactory;
	}

	/**
	 * Save Migration
	 *
	 * @param  MigrationLogModel  $model
	 */
	public function save( MigrationLogModel $model ) {
		$query = "
			INSERT INTO {$this->migration_table} (id, status, error, last_run )
			VALUES (%s, %s, %s, NOW())
			ON DUPLICATE KEY UPDATE
			status = %s,
			error = %s,
			last_run = NOW()
		";

		DB::query(
			DB::prepare(
				$query,
				$model->getId(),
				$model->getStatus(),
				$model->getError(),
				$model->getStatus(),
				$model->getError()
			)
		);
	}

	/**
	 * Get all migrations
	 *
	 * @return MigrationLogModel[]
	 */
	public function getMigrations() {
		$migrations = [];

		$result = DB::get_results( "SELECT * FROM {$this->migration_table}" );

		if ( $result ) {
			foreach ( $result as $migration ) {

				$migrations[] = $this->migrationFactory->make(
					$migration->id,
					$migration->status,
					$migration->error,
					$migration->last_run
				);
			}
		}

		return $migrations;
	}

	/**
	 * Get migration by ID
	 *
	 * @param string $id
	 *
	 * @return MigrationLogModel|null
	 */
	public function getMigration( $id ) {
		$migration = DB::get_row(
			DB::prepare( "SELECT * FROM {$this->migration_table} WHERE id = %s", $id )
		);

		if ( $migration ) {
			return $this->migrationFactory->make(
				$migration->id,
				$migration->status,
				$migration->error,
				$migration->last_run
			);
		}

		return null;
	}

	/**
	 * Get migrations by status
	 *
	 * @param  string  $status
	 *
	 * @return MigrationLogModel[]
	 */
	public function getMigrationsByStatus( $status ) {
		$migrations = [];

		$result = DB::get_results(
			DB::prepare( "SELECT * FROM {$this->migration_table} WHERE status = %s", $status )
		);

		if ( $result ) {
			foreach ( $result as $migration ) {
				$migrations[] = $this->migrationFactory->make(
					$migration->id,
					$migration->status,
					$migration->error,
					$migration->last_run
				);
			}
		}

		return $migrations;
	}

	/**
	 * Get completed migrations IDs
	 *
	 * @return array
	 */
	public function getCompletedMigrationsIDs() {
		$migrations = [];

		try {
			$result = DB::get_results(
				DB::prepare( "SELECT * FROM {$this->migration_table} WHERE status = %s", MigrationLogStatus::SUCCESS )
			);
		} catch ( \Exception $exception ) {
			// This exception should happen only once, during the migration system storage update.
			// But, we will log this error just in case to see if this is a repeating problem.
			error_log( $exception->getMessage() );

			// Fallback to legacy migration storage system
			return get_option( 'give_database_migrations', [] );
		}

		if ( $result ) {
			foreach ( $result as $migration ) {
				$migrations[] = $migration->id;
			}
		}

		return $migrations;
	}

	/**
	 * Get migration count
	 *
	 * @return int|null
	 */
	public function getMigrationsCount() {
		try {
			return DB::get_var( "SELECT count(id) FROM {$this->migration_table}" );
		} catch ( \Exception $exception ) {
			return 0;
		}
	}

	/**
	 * Get failed migrations count by list of migrations ids
	 *
	 * @param array $migrationIds
	 *
	 * @return int
	 */
	public function getFailedMigrationsCountByIds( $migrationIds ) {
		try {
			$query = sprintf(
				"SELECT count(id) FROM %s WHERE id IN ('%s') AND status != '%s'",
				$this->migration_table,
				implode( "','", array_map( 'esc_sql', $migrationIds ) ),
				MigrationLogStatus::SUCCESS
			);

			return DB::get_var( $query );
		} catch ( \Exception $exception ) {
			// Fallback
			return 0;
		}
	}

}
