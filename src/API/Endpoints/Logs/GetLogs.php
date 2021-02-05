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
	protected $endpoint = 'logs/get-logs';

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
						'type'      => [
							'validate_callback' => function( $param ) {
								if ( 'all' === $param ) {
									return true;
								}
								return LogType::isValid( $param );
							},
							'default'           => 'all',
						],
						'sort'      => [
							'validate_callback' => function( $param ) {
								if ( empty( $param ) ) {
									return true;
								}
								return in_array( $param, $this->logRepository->getSortableColumns(), true );
							},
							'default'           => 'id',
						],
						'direction' => [
							'validate_callback' => function( $param ) {
								if ( empty( $param ) ) {
									return true;
								}
								return in_array( strtoupper( $param ), [ 'ASC', 'DESC' ], true );
							},
							'default'           => 'DESC',
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
				'type'      => [
					'type'        => 'string',
					'description' => esc_html__( 'Log type', 'give' ),
				],
				'direction' => [
					'type'        => 'string',
					'description' => esc_html__( 'Sort direction', 'give' ),
				],
				'sort'      => [
					'type'        => 'string',
					'description' => esc_html__( 'Sort by column', 'give' ),
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

		$type      = $request->get_param( 'type' );
		$category  = $request->get_param( 'category' );
		$page      = $request->get_param( 'page' );
		$sortBy    = $request->get_param( 'sort' );
		$direction = $request->get_param( 'direction' );

		// By type
		if ( 'all' !== $type ) {
			$logs  = $this->logRepository->getLogsByType( $type );
			$total = $this->logRepository->getLogCountBy( 'log_type', $type );
		}
		// By category
		elseif ( ! empty( $category ) ) {
			$logs  = $this->logRepository->getLogsByCategory( $category );
			$total = $this->logRepository->getLogCountBy( 'category', $category );
		}
		// Get all
		else {
			$logs  = $this->logRepository->getLogs( 10, ( $page * 10 ), $sortBy, $direction );
			$total = $this->logRepository->getTotalCount();
		}

		foreach ( $logs as $log ) {
			$data[] = [
				'id'       => $log->getId(),
				'log_type' => $log->getType(),
				'category' => $log->getCategory(),
				'source'   => $log->getSource(),
				'message'  => $log->getMessage(),
				'context'  => $log->getContext(),
				'date'     => date( 'd.m.Y' ) . ' - ' . $log->getId(), // todo: log model doesnt have date property
			];
		}

		return new WP_REST_Response(
			[
				'status' => true,
				'data'   => $data,
				'total'  => floor( $total / 10 ),
			]
		);
	}

}
