<?php

namespace Give\MigrationLog\Migrations;

use Give\MigrationLog\MigrationLogStatus;
use Give\MigrationLog\MigrationLogFactory;
use Give\Framework\Migrations\Contracts\Migration;


/**
 * Class MigrateCompletedMigrations
 * @package Give\MigrationLog\Migrations
 *
 * @since 2.10.0
 */
class MigrateCompletedMigrations extends Migration {
	/**
	 * @var MigrationLogFactory
	 */
	private $migrationLogFactory;

	/**
	 * MigrateCompletedMigrations constructor.
	 *
	 * @param  MigrationLogFactory  $migrationLogFactory
	 */
	public function __construct( MigrationLogFactory $migrationLogFactory ) {
		$this->migrationLogFactory = $migrationLogFactory;
	}
	/**
	 * @return string
	 */
	public static function id() {
		return 'migrate_completed_migrations';
	}

	/**
	 * @return string
	 */
	public static function title() {
		return  esc_html__( 'Migrate completed migrations to give_migrations table' );
	}

	/**
	 * @return int
	 */
	public static function timestamp() {
		return strtotime( '1970-01-02 00:00' );
	}


	public function run() {
		$migrations = get_option( 'give_database_migrations', [] );

		foreach ( $migrations as $migrationId ) {
			$migrationLog = $this->migrationLogFactory->make( $migrationId );
			$migrationLog->setStatus( MigrationLogStatus::SUCCESS );
			$migrationLog->save();
		}
	}
}
