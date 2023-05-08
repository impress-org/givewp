<?php

namespace Give\API\Endpoints\Reports;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Recommended Addon endpoint
 *
 * @unreleased
 */
class ProductRecommendation extends Endpoint
{
    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->endpoint = 'product-recommendation';
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function registerRoute()
    {
        register_rest_route(
            $this->endpoint,
            [
                [
                    'methods' => ['POST'],
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                    'args' => [
                        'option' => [
                            'type' => 'string',
                            'required' => true,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $errors = [];
        $successes = [];

        try {
            $option = $request->get_param('option');
            if ($option === 'givewp_reports_recurring_recommendation_dismissed') {
                update_option('givewp_reports_recurring_recommendation_dismissed', time());
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        if (count($errors) > 0) {
            $response = new WP_Error('invalid_option', __('Invalid option'), ['status' => 400]);
        } else {
            $response = [
                'successes' => $successes,
            ];
        }

        return new WP_REST_Response($response);
    }
}
