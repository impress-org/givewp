<?php

namespace Give\MigrationLog;

use WP_REST_Request;
use Give\Framework\Database\DB;

/**
 * Class MigrationLogRepository
 * @package Give\MigrationLog
 *
 * @since 2.10.0
 */
class MigrationLogRepository {
	/**
	 * Limit number of logs returned per page
	 */
	const MIGRATIONS_PER_PAGE = 50;

	/**
	 * Define sortable columns
	 */
	const SORTABLE_COLUMNS = [ 'id', 'status', 'last_run', 'run_order' ];

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
		$this->migration_table  = "{$wpdb->prefix}give_migrations";
		$this->migrationFactory = $migrationFactory;
	}

	/**
	 * Save Migration
	 *
	 * @param  MigrationLogModel  $model
	 */
	public function save( MigrationLogModel $model ) {
		$query = "
			INSERT INTO {$this->migration_table} (id, status, error, run_order ) 
			VALUES (%s, %s, %s, %s) 
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
				$model->getRunOrder(),
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

		$result = DB::get_results( "SELECT * FROM {$this->migration_table} ORDER BY run_order ASC" );

		if ( $result ) {
			foreach ( $result as $migration ) {

				$migrations[] = $this->migrationFactory->make(
					$migration->id,
					$migration->status,
					$migration->error,
					$migration->last_run,
					$migration->run_order
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
				$migration->last_run,
				$migration->run_order
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
			DB::prepare( "SELECT * FROM {$this->migration_table} WHERE status = %s ORDER BY run_order ASC", $status )
		);

		if ( $result ) {
			foreach ( $result as $migration ) {
				$migrations[] = $this->migrationFactory->make(
					$migration->id,
					$migration->status,
					$migration->error,
					$migration->last_run,
					$migration->run_order
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
	 * Get sortable columns
	 *
	 * @return string[]
	 */
	public function getSortableColumns() {
		return self::SORTABLE_COLUMNS;
	}

	/**
	 * Get migrations per page limit
	 *
	 * @return int
	 */
	public function getMigrationsPerPageLimit() {
		return self::MIGRATIONS_PER_PAGE;
	}

	/**
	 * Get all migrations for request
	 *
	 * @param  WP_REST_Request  $request
	 *
	 * @return MigrationLogModel[]
	 */
	public function getMigrationsForRequest( WP_REST_Request $request ) {
		$migrations    = [];
		$status        = $request->get_param( 'status' );
		$page          = $request->get_param( 'page' );
		$sortBy        = $request->get_param( 'sort' );
		$sortDirection = $request->get_param( 'direction' );

		$perPage = self::MIGRATIONS_PER_PAGE;
		$offset  = ( $page - 1 ) * $perPage;

		$query = "SELECT * FROM {$this->migration_table} WHERE 1=1";

		if ( ! empty( $status ) && 'all' !== $status ) {
			$query .= sprintf( ' AND status = "%s"', esc_sql( $status ) );
		}

		if ( $sortBy ) {
			$column    = ( in_array( $sortBy, self::SORTABLE_COLUMNS, true ) ) ? $sortBy : 'run_order';
			$direction = ( $sortDirection && strtoupper( $sortDirection ) === 'ASC' ) ? 'ASC' : 'DESC';

			$query .= " ORDER BY `{$column}` {$direction}";
		} else {
			$query .= ' ORDER BY id DESC';
		}

		// Limit
		$query .= sprintf( ' LIMIT %d', self::MIGRATIONS_PER_PAGE );

		// Offset
		if ( $offset > 1 ) {
			$query .= sprintf( ' OFFSET %d', $offset );
		}

		$result = DB::get_results( $query );

		if ( $result ) {
			foreach ( $result as $migration ) {
				$migrations[] = $this->migrationFactory->make(
					$migration->id,
					$migration->status,
					$migration->error,
					$migration->last_run,
					$migration->run_order
				);
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
	 * Get failed migrations count
	 *
	 * @return int
	 */
	public function getFailedMigrationsCount() {
		try {
			return DB::get_var(
				DB::prepare( "SELECT count(id) FROM {$this->migration_table} WHERE status != %s", MigrationLogStatus::SUCCESS )
			);
		} catch ( \Exception $exception ) {
			// Fallback
			return 0;
		}
	}

}
