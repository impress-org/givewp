<?php

namespace Give\Donations\Endpoints;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class RecommendedProducts extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/recommended-options';

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
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
                            'enum' => [
                                'givewp_recurring_recommendation_dismissed',
                                'givewp_fee_recovery_recommendation_dismissed',
                                'givewp_designated_funds_recommendation_dismissed',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You don\'t have permission to edit Donations', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
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
            switch ($request->get_param('option')) {
                case 'givewp_donations_recurring_recommendation_dismissed':
                    update_option('givewp_recurring_recommendation_dismissed', true);
                    $successes[] = 'givewp_recurring_recommendation_dismissed';
                    break;

                case 'givewp_donations_fee_recovery_recommendation_dismissed' :
                    update_option('givewp_fee_recovery_recommendation_dismissed', true);
                    $successes[] = 'givewp_fee_recovery_recommendation_dismissed';
                    break;

                case 'givewp_donations_designated_funds_recommendation_dismissed':
                    update_option('givewp_designated_funds_recommendation_dismissed', true);
                    $successes[] = 'givewp_designated_funds_recommendation_dismissed';
                    break;

                default:
                    $errors[] = 'Invalid option';
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
