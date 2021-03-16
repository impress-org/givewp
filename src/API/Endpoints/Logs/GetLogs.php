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
 * @since 2.10.0
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
						'page'      => [
							'validate_callback' => function( $param ) {
								return filter_var( $param, FILTER_VALIDATE_INT );
							},
							'default'           => '1',
						],
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
				'page'      => [
					'type'        => 'integer',
					'description' => esc_html__( 'Current page', 'give' ),
				],
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
		$data  = [];
		$logs  = $this->logRepository->getLogsForRequest( $request );
		$total = $this->logRepository->getLogCountForRequest( $request );

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
				'pages'      => ceil( $total / $this->logRepository->getLogsPerPageLimit() ),
				'categories' => $this->logRepository->getCategories(),
				'sources'    => $this->logRepository->getSources(),
				'statuses'   => LogType::getTypesTranslated(),
			]
		);
	}

}
