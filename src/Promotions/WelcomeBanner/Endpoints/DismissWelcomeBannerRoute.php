<?php

namespace Give\Promotions\WelcomeBanner\Endpoints;

use Give\API\RestRoute;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class DismissWelcomeBannerRoute implements RestRoute
{
    /**
     * @var string
     * @since 3.0.0
     */
    protected $endpoint = 'welcome-banner/dismiss';

    /**
     * @inheritDoc
     * @since 3.0.0
     */
    public function registerRoute(): void
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
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
     * @since 3.0.0
     */
    public function permissionsCheck()
    {
        if (!current_user_can('manage_options')) {
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
     * @since 3.0.0
     */
    public function authorizationStatusCode(): int
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
     * @since 3.0.0
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        update_option($request->get_param('action'), time());

        return new WP_REST_Response(['banner_dismissed' => $request->get_param('action')]);
    }

}
