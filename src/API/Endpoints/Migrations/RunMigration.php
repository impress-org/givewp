<?php

namespace Give\API\Endpoints\Migrations;

use Exception;
use WP_REST_Request;
use WP_REST_Response;
use Give\MigrationLog\MigrationLogStatus;
use Give\MigrationLog\MigrationLogFactory;
use Give\MigrationLog\MigrationLogRepository;
use Give\Framework\Migrations\MigrationsRegister;

/**
 * Class RunMigration
 * @package Give\API\Endpoints\Migrations
 *
 * @since 2.10.0
 */
class RunMigration extends Endpoint {

	/** @var string */
	protected $endpoint = 'migrations/run-migration';

	/**
	 * @var MigrationsRegister
	 */
	private $migrationsRegister;

	/**
	 * @var MigrationLogRepository
	 */
	private $migrationLogRepository;

	/**
	 * @var MigrationLogFactory
	 */
	private $migrationLogFactory;

	/**
	 * RunMigration constructor.
	 *
	 * @param  MigrationsRegister  $migrationsRegister
	 * @param  MigrationLogRepository  $migrationLogRepository
	 * @param  MigrationLogFactory  $migrationLogFactory
	 */
	public function __construct(
		MigrationsRegister $migrationsRegister,
		MigrationLogRepository $migrationLogRepository,
		MigrationLogFactory $migrationLogFactory
	) {
		$this->migrationsRegister     = $migrationsRegister;
		$this->migrationLogRepository = $migrationLogRepository;
		$this->migrationLogFactory    = $migrationLogFactory;
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
					'methods'             => 'POST',
					'callback'            => [ $this, 'handleRequest' ],
					'permission_callback' => [ $this, 'permissionsCheck' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return ! empty( trim( $param ) );
							},
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
				'id' => [
					'type'        => 'string',
					'description' => esc_html__( 'Migration ID', 'give' ),
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
		global $wpdb;
		$migrationId    = $request->get_param( 'id' );
		$migrationClass = $this->migrationsRegister->getMigration( $migrationId );
		$migrationLog   = $this->migrationLogFactory->make( $migrationId );

		// Begin transaction
		$wpdb->query( 'START TRANSACTION' );

		try {
			$migration = give( $migrationClass );
			$migration->run();
			// Save migration status
			$migrationLog->setStatus( MigrationLogStatus::SUCCESS );
			$migrationLog->save();

			return new WP_REST_Response(
				[
					'status'  => true,
					'message' => 'Migration ',
				]
			);

		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK' );

			$migrationLog->setStatus( MigrationLogStatus::FAILED );
			$migrationLog->setError( $exception );
			$migrationLog->save();
		}

		return new WP_REST_Response(
			[
				'status'  => false,
				'message' => 'Something went wrong',
			]
		);
	}

}
