<?php declare(strict_types=1);

namespace Give\API\REST\V3\Routes\Status;

use Give\API\REST\V3\Routes\Status\ValueObjects\StatusRoute;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;

/**
 * A simple endpoint to verify the GiveWP REST API is working.
 *
 * @tbd
 */
class StatusController extends WP_REST_Controller
{

    public const STATUS = 'status';
    public const OK = 'ok';

    public function __construct()
    {
        $this->namespace = StatusRoute::NAMESPACE;
        $this->rest_base = StatusRoute::BASE;
    }

    /**
     * @tbd
     */
    public function register_routes(): void
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'getStatus'],
                'permission_callback' => '__return_true',
            ],
            'schema' => [$this, 'getSchema'],
        ]);
    }

    /**
     * @tbd
     *
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function getStatus()
    {
        return rest_ensure_response([
            self::STATUS => self::OK,
        ]);
    }

    /**
     * @tbd
     *
     * @return array<string, mixed>
     */
    public function getSchema(): array
    {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'status',
            'type'       => 'object',
            'properties' => [
                self::STATUS => [
                    'description' => esc_html__( 'Whether the GiveWP REST API is registered and responding.', 'give' ),
                    'type'        => 'string',
                    'enum'        => [ self::OK ],
                    'readonly'    => true,
                ],
            ],
        ];
    }

}
