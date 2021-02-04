<?php

namespace Give\API\Endpoints\Logs;

use WP_REST_Request;
use WP_REST_Response;
use Give\Log\LogRepository;
use Give\Log\ValueObjects\LogType;

/**
 * Class GetLogs
 * @package Give\API\Endpoints\Logs
 *
 * @since 2.9.7
 */
class GetLogs extends Endpoint {

	/** @var string */
	protected $endpoint = 'logs/get';
	//protected $endpoint = 'logs/get(?:/(?P<type>\s+))?(?:/(?P<limit>\d+))?(?:/(?P<offset>\d+))'; todo: check what is wrong with this

	/**
	 * @var LogRepository
	 */
	private $logRepository;

	/**
	 * GetLogs constructor.
	 *
	 * @param  LogRepository  $repository
	 */
	public function __construct( LogRepository $repository ) {
		$this->logRepository = $repository;
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
						'type'   => [
							'validate_callback' => function( $param ) {
								if ( 'all' === $param ) {
									return true;
								}
								return LogType::isValid( $param );
							},
						],
						'limit'  => [
							'validate_callback' => function( $param ) {
								return filter_var( FILTER_VALIDATE_INT, $param );
							},
						],
						'offset' => [
							'validate_callback' => function( $param ) {
								return filter_var( FILTER_VALIDATE_INT, $param );
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
				'type'   => [
					'type'        => 'string',
					'description' => esc_html__( 'Log type', 'give' ),
				],
				'limit'  => [
					'type'        => 'integer',
					'description' => esc_html__( 'Limit number of logs returned', 'give' ),
				],
				'offset' => [
					'type'        => 'integer',
					'description' => esc_html__( 'Set offset', 'give' ),
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
		$data = [];

		$type = $request->get_param( 'type' );

		$logs = ( ! empty( $type ) && 'all' !== $type )
			? $this->logRepository->getLogsByType( $type )
			: $this->logRepository->getLogs();

		foreach ( $logs as $log ) {
			$data[] = [
				'id'       => $log->getId(),
				'type'     => $log->getType(),
				'category' => $log->getCategory(),
				'source'   => $log->getSource(),
				'message'  => $log->getMessage(),
				'context'  => $log->getContext(),
				'date'     => date( 'd.m.Y' ), // todo: log model doesnt have date property
			];
		}

		return new WP_REST_Response(
			[
				'status' => true,
				'data'   => $data,
			]
		);
	}

}
