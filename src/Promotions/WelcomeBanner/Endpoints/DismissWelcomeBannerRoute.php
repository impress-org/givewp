<?php

namespace Give\Promotions\WelcomeBanner\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

class DismissWelcomeBannerRoute
{


    /**
     * @var string
     * @unreleased
     */
    protected $endpoint = 'admin/welcome-banner';

    /**
     * @inheritDoc
     * @unreleased
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => 'is_user_logged_in',
                    'args' => [
                        'action' => [
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
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__(
                    'You don\'t have permission to dismiss options. Only users with the "manage_options" capability can perform this action.',
                    'give'
                ),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * Sets up the proper HTTP status code for authorization.
     * @return int
     * @unreleased
     */
    public function authorizationStatusCode()
    {
        if (is_user_logged_in()) {
            return 403;
        }

        return 401;
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     * @unreleased
     */
    public function handleRequest(WP_REST_Request $request)
    {
        update_option($request->get_param('action'), time());

        return new WP_REST_Response(['banner_dismissed' => $request->get_param('action')]);
    }

}
