<?php

namespace Give\Campaigns\Routes\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use WP_REST_Response;

/**
 * @unreleased
 */
trait RestResponses
{
    /**
     * @unreleased
     */
    public function notFoundResponse($message, $response = 'rest_not_found'): WP_REST_Response
    {
        return new WP_REST_Response(
            [
                'status' => 404,
                'response' => $response,
                'body_response' => [
                    'message' => html_entity_decode(
                        $message
                    ),
                ],
            ]
        );
    }

    /**
     * @unreleased
     */
    public function badRequestResponse(Exception $e, $response = 'rest_bad_request'): WP_REST_Response
    {
        return new WP_REST_Response(
            [
                'status' => 400,
                'response' => $response,
                'body_response' => [
                    'message' => html_entity_decode(
                        sprintf(
                            esc_html__('%s. Search the logs at Donations > Tools > Logs for a more specific cause of the problem.',
                                'give'), rtrim($e->getMessage(), '.')
                        )
                    ),
                ],
            ]
        );
    }
}
