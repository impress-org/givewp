<?php

namespace Give\Promotions\InPluginUpsells\Endpoints;

use Give\Donations\Endpoints\Endpoint;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;


class ProductRecommendationsRoute extends Endpoint
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
                                'givewp_donations_recurring_recommendation_dismissed',
                                'givewp_donations_fee_recovery_recommendation_dismissed',
                                'givewp_donations_designated_funds_recommendation_dismissed',
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
                esc_html__('You don\'t have permission to dismiss options. Only users with the "manage_options" capability can perform this action.',
                    'give'),
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
                    update_option('givewp_donations_recurring_recommendation_dismissed', true);
                    $successes[] = 'givewp_donations_recurring_recommendation_dismissed';
                    break;

                case 'givewp_donations_fee_recovery_recommendation_dismissed' :
                    update_option('givewp_donations_fee_recovery_recommendation_dismissed', true);
                    $successes[] = 'givewp_donations_fee_recovery_recommendation_dismissed';
                    break;

                case 'givewp_donations_designated_funds_recommendation_dismissed':
                    update_option('givewp_donations_designated_funds_recommendation_dismissed', true);
                    $successes[] = 'givewp_donations_designated_funds_recommendation_dismissed';
                    break;

                default:
                    $errors[] = "Invalid option: {$request->get_param('option')}";
            }
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        if (count($errors) > 0) {
            $response = new WP_Error('invalid_option', __('Invalid option'), ['status' => 400]);

            Log::error('Invalid option', [
                'Error' => $errors,
                'Response' => $response,
            ]);
        } else {
            $response = [
                'successes' => $successes,
            ];
        }

        return new WP_REST_Response($response);
    }
}
