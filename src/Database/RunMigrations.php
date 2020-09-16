<?php
namespace Give\Database;

use Give\Framework\Migration;
use Give\Database\Migrations\CreateRevenueTable;
use http\Exception\InvalidArgumentException;

/**
 * Class RunMigrations
 * @package Give\Database\Migration
 *
 * @since 2.9.0
 */
class RunMigrations {
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
	private $completedMigrations = [];

	/**
	 *  RunMigrations constructor.
	 */
	public function __construct() {
		$this->completedMigrations = get_option( $this->optionNameToStoreCompletedMigrations, [] );
	}

	/**
	 * List of database migrations.
	 *
	 * @since 2.9.0
	 *
	 * @var string[]
	 */
	private $migrations = [
		CreateRevenueTable::class,
	];

	/**
	 * Run database migrations.
	 *
	 * @since 2.9.0
	 */
	public function run() {
		if ( ! $this->hasMigrationToRun() ) {
			return;
		}

		/* @var Migration $className */
		foreach ( $this->migrations as $index => $className ) {
			unset( $this->migrations[ $index ] );

			$this->migrations[ $className::timestamp() ] = $className;
		}

		ksort( $this->migrations );

		$newMigrations = [];

		// Process migrations.
		foreach ( $this->migrations as $migration ) {
			if ( in_array( $migration, $this->completedMigrations, true ) ) {
				continue;
			}

			$migration::run();
			$newMigrations[] = $migration;
		}

		// Save processed migrations.
		if ( $newMigrations ) {
			update_option(
				$this->optionNameToStoreCompletedMigrations,
				array_merge( $this->completedMigrations, $newMigrations )
			);
		}
	}

	/**
	 * Register database migration.
	 *
	 * @since 2.9.0
	 *
	 * @param string $class Class name.
	 */
	public function register( $class ) {
		if ( in_array( $class, $this->migrations, true ) ) {
			throw new InvalidArgumentException( 'Please add database migration with unique class name' );
		}

		$this->migrations = $class;
	}

	/**
	 * Return whether or not all migrations completed.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function hasMigrationToRun() {
		return (bool) array_diff( $this->migrations, $this->completedMigrations );
	}
}
