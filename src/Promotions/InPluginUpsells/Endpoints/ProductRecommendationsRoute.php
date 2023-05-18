<?php

namespace Give\Promotions\InPluginUpsells\Endpoints;

use Give\API\RestRoute;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;


class ProductRecommendationsRoute implements RestRoute
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/recommended-options';

    /**
     * @inheritDoc
     *
     * @since 2.27.1
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
                                'givewp_reports_recurring_recommendation_dismissed',
                                'givewp_reports_fee_recovery_recommendation_dismissed',
                                'givewp_donors_fee_recovery_recommendation_dismissed',
                                'givewp_form_editor_donation_options_recurring_recommendation',
                                'givewp_payment_gateway_fee_recovery_recommendation',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @since 2.27.1
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
     * Sets up the proper HTTP status code for authorization.
     * @since 2.27.1
     *
     * @return int
     */
    public function authorizationStatusCode()
    {
        if (is_user_logged_in()) {
            return 403;
        }

        return 401;
    }

    /**
     * @since 2.27.1
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        update_option($request->get_param('option'), time());

        return new WP_REST_Response(['option_updated' => $request->get_param('option')]);
    }
}
