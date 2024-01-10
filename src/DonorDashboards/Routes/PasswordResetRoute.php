<?php

namespace Give\DonorDashboards\Routes;

use Give\API\RestRoute;
use Give\DonorDashboards\Helpers as DonorDashboardHelpers;
use WP_REST_Request;
use WP_REST_Response;


/**
 * @since 3.3.0
 */
class PasswordResetRoute implements RestRoute
{

    /** @var string */
    protected $endpoint = 'donor-dashboard/reset-password';

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
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'email' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }

    /**
     * Handles logout request
     *
     * @since 3.3.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     *
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $sent = retrieve_password($request->get_param('email'));

        return $sent
            ? new WP_REST_Response(
                [
                    'status' => 200,
                    'response' => 'password_reset_sent',
                    'body_response' => [
                        'message' => __('The password reset email has been sent.', 'give'),
                    ],
                ]
            )
            : new WP_REST_Response(
                [
                    'status' => 400,
                    'response' => 'error',
                    'body_response' => [
                        'error' => 'password_reset_failed',
                        'message' => esc_html__('Unable to reset password. Please try again.', 'give'),
                    ],
                ]
            );
    }

    /**
     * Check permissions
     */
    public function permissionsCheck(): bool
    {
        return DonorDashboardHelpers::isDonorLoggedIn();
    }
}
