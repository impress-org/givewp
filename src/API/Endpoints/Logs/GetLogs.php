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
								if ( empty( $param ) || ( 'all' === $param ) ) {
									return true;
								}
								return LogType::isValid( $param );
							},
							'default'           => 'all',
						],
						'category'  => [
							'validate_callback' => function( $param ) {
								return is_string( $param );
							},
							'default'           => '',
						],
						'source'    => [
							'validate_callback' => function( $param ) {
								return is_string( $param );
							},
							'default'           => '',
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
				'category'  => [
					'type'        => 'string',
					'description' => esc_html__( 'Log category', 'give' ),
				],
				'source'    => [
					'type'        => 'string',
					'description' => esc_html__( 'Log source', 'give' ),
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
		$source    = $request->get_param( 'source' );
		$page      = $request->get_param( 'page' );
		$sortBy    = $request->get_param( 'sort' );
		$direction = $request->get_param( 'direction' );

		// Pagination
		$perPage = 10;
		$offset  = $page > 1 ? $page * $perPage : 0;

		$logs  = $this->logRepository->getLogs( $type, $category, $source, $perPage, $offset, $sortBy, $direction );
		$total = $this->logRepository->getLogCountForColumns( $type, $category, $source );

		foreach ( $logs as $log ) {
			$data[] = [
				'id'       => $log->getId(),
				'log_type' => $log->getType(),
				'category' => $log->getCategory(),
				'source'   => $log->getSource(),
				'message'  => $log->getMessage(),
				'context'  => $log->getContext(),
				'date'     => $log->getDate(),
			];
		}

		return new WP_REST_Response(
			[
				'status'     => true,
				'data'       => $data,
				'total'      => floor( $total / $perPage ),
				'categories' => $this->logRepository->getCategories(),
				'sources'    => $this->logRepository->getSources(),
				'statuses'   => LogType::getAll(),
				'type'       => $type,
			]
		);
	}

}
