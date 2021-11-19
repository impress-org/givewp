<?php

namespace Give\API\Endpoints\Logs;

use Give\Log\LogRepository;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class FlushLogs
 * @package Give\API\Endpoints\Logs
 *
 * @since 2.10.0
 */
class FlushLogs extends Endpoint
{

    /** @var string */
    protected $endpoint = 'logs/flush-logs';

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * GetLogs constructor.
     *
     * @param LogRepository $repository
     */
    public function __construct(LogRepository $repository)
    {
        $this->logRepository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'DELETE',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                    'args' => [],
                ],
                'schema' => [$this, 'getSchema'],
            ]
        );
    }

    /**
     * @return array
     */
    public function getSchema()
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'logs',
            'type' => 'object',
            'properties' => [],
        ];
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $this->logRepository->flushLogs();

        return new WP_REST_Response(['status' => true]);
    }

}
