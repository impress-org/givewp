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
	/**
	 * Enable sorting by these columns
	 */
	const SORTABLE_COLUMNS = [ 'id', 'status', 'last_run', 'run_order', 'title', 'source' ];

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
						'page'      => [
							'validate_callback' => function( $param ) {
								return filter_var( $param, FILTER_VALIDATE_INT );
							},
							'default'           => '1',
						],
						'sort'      => [
							'validate_callback' => function( $param ) {
								if ( empty( $param ) ) {
									return true;
								}
								return in_array( $param, self::SORTABLE_COLUMNS, true );
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
				'page'      => [
					'type'        => 'integer',
					'description' => esc_html__( 'Current page', 'give' ),
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
		$migrationsPerPage = 10;
		$migrations        = $this->migrationRepository->getMigrations();
		$migrationsCount   = count( $this->migrationRegister->getRegisteredIds() );
		$pendingMigrations = $this->migrationHelper->getPendingMigrations();

		foreach ( $migrations as $migration ) {
			// Get only registered migrations
			if ( ! $this->migrationRegister->hasMigration( $migration->getId() ) ) {
				continue;
			}

			/* @var Migration $migrationClass */
			$migrationClass = $this->migrationRegister->getMigration( $migration->getId() );

			$data[] = [
				'id'        => $migration->getId(),
				'status'    => $migration->getStatus(),
				'error'     => $migration->getError(),
				'last_run'  => $migration->getLastRunDate(),
				'run_order' => $this->migrationHelper->getRunOrderForMigration( $migration->getId() ),
				'source'    => $migrationClass::source(),
				'title'     => $migrationClass::title(),
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
				'source'    => $migration::source(),
				'title'     => $migration::title(),
			];
		}

		// Sort migrations
		$sortColumn    = array_column( $data, $request->get_param( 'sort' ) );
		$sortDirection = ( 'DESC' === strtoupper( $request->get_param( 'direction' ) ) ) ? SORT_DESC : SORT_ASC;

		array_multisort( $sortColumn, $sortDirection, $data );

		// Pagination
		$page   = $request->get_param( 'page' );
		$offset = ( $page - 1 ) * $migrationsPerPage;
		$data   = array_slice( $data, $offset, $migrationsPerPage );

		return new WP_REST_Response(
			[
				'status'      => true,
				'data'        => $data,
				'pages'       => ceil( $migrationsCount / $migrationsPerPage ),
				'showOptions' => 'enabled' === give_get_option( 'enable_database_updates' ),
			]
		);
	}

}
