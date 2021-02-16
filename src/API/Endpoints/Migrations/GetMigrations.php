<?php

namespace Give\API\Endpoints\Migrations;

use WP_REST_Request;
use WP_REST_Response;
use Give\MigrationLog\MigrationLogStatus;
use Give\MigrationLog\MigrationLogRepository;
use Give\MigrationLog\Helpers\MigrationHelper;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\MigrationsRegister;

/**
 * Class GetMigrations
 * @package Give\API\Endpoints\Logs
 *
 * @since 2.10.0
 */
class GetMigrations extends Endpoint {

	/** @var string */
	protected $endpoint = 'migrations/get-migrations';

	/**
	 * @var MigrationLogRepository
	 */
	private $migrationRepository;

	/**
	 * @var MigrationHelper
	 */
	private $migrationHelper;

	/**
	 * @var MigrationsRegister
	 */
	private $migrationRegister;

	/**
	 * GetLogs constructor.
	 *
	 * @param  MigrationLogRepository  $repository
	 * @param  MigrationHelper  $migrationHelper
	 * @param  MigrationsRegister  $migrationRegister
	 */
	public function __construct(
		MigrationLogRepository $repository,
		MigrationHelper $migrationHelper,
		MigrationsRegister $migrationRegister
	) {
		$this->migrationRepository = $repository;
		$this->migrationHelper     = $migrationHelper;
		$this->migrationRegister   = $migrationRegister;
	}

	/**
	 * @inheritDoc
	 */
	public function registerRoute() {
		register_rest_route(
			'give-api/v2',
			$this->endpoint,
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => [ $this, 'permissionsCheck' ],
					'args'                => [
						'status'    => [
							'validate_callback' => function( $param ) {
								if ( empty( $param ) || ( 'all' === $param ) ) {
									return true;
								}
								return MigrationLogStatus::isValid( $param );
							},
							'default'           => 'all',
						],
						'sort'      => [
							'validate_callback' => function( $param ) {
								if ( empty( $param ) ) {
									return true;
								}
								return in_array( $param, $this->migrationRepository->getSortableColumns(), true );
							},
							'default'           => 'run_order',
						],
						'direction' => [
							'validate_callback' => function( $param ) {
								if ( empty( $param ) ) {
									return true;
								}
								return in_array( strtoupper( $param ), [ 'ASC', 'DESC' ], true );
							},
							'default'           => 'ASC',
						],
					],
				],
				'schema' => [ $this, 'getSchema' ],
			]
		);
	}

	/**
	 * @return array
	 */
	public function getSchema() {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'logs',
			'type'       => 'object',
			'properties' => [
				'status'    => [
					'type'        => 'string',
					'description' => esc_html__( 'Migration status', 'give' ),
				],
				'sort'      => [
					'type'        => 'string',
					'description' => esc_html__( 'Sort by column', 'give' ),
				],
				'direction' => [
					'type'        => 'string',
					'description' => esc_html__( 'Sort direction', 'give' ),
				],
			],
		];
	}

	/**
	 * @param  WP_REST_Request  $request
	 *
	 * @return WP_REST_Response
	 */
	public function handleRequest( WP_REST_Request $request ) {
		$data              = [];
		$migrations        = $this->migrationRepository->getMigrationsForRequest( $request );
		$migrationsCount   = $this->migrationRepository->getMigrationsCount();
		$pendingMigrations = $this->migrationHelper->getPendingMigrations();

		foreach ( $migrations as $migration ) {
			$data[] = [
				'id'        => $migration->getId(),
				'status'    => $migration->getStatus(),
				'error'     => $migration->getError(),
				'last_run'  => $migration->getLastRunDate(),
				'run_order' => $this->migrationHelper->getRunOrderForMigration( $migration->getId() ),
			];
		}

		// Check for pending migrations
		/* @var Migration $migration */
		foreach ( $pendingMigrations as $migration ) {
			$data[] = [
				'id'        => $migration::id(),
				'status'    => MigrationLogStatus::PENDING,
				'error'     => '',
				'last_run'  => '',
				'run_order' => $this->migrationHelper->getRunOrderForMigration( $migration::id() ),
			];
		}

		return new WP_REST_Response(
			[
				'status'      => true,
				'data'        => $data,
				'pages'       => ceil( $migrationsCount / $this->migrationRepository->getMigrationsPerPageLimit() ),
				'showOptions' => 'enabled' === give_get_option( 'enable_database_updates' ),
			]
		);
	}

}
