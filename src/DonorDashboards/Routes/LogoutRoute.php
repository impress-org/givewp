<?php

namespace Give\DonorDashboards\Routes;

use Give\API\RestRoute;
use Give\DonorDashboards\Helpers as DonorDashboardHelpers;
use WP_REST_Request;
use WP_REST_Response;


/**
 * @since 2.10.0
 */
class LogoutRoute implements RestRoute
{

    /** @var string */
    protected $endpoint = 'donor-dashboard/logout';

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
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
            ]
        );
    }

    /**
     * Handles logout request
     *
     * @since 2.10.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     *
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        // Check if WP user is logged in
        if (get_current_user_id() !== 0) {
            // Handle logout logic for WP users

            /**
             * Fires before processing user logout.
             *
             * @since 1.0
             */
            do_action('give_before_user_logout');

            // Logout user (and destroys current Give Session, via hook registered in Give_Session class)
            wp_logout();

            /**
             * Fires after processing user logout.
             *
             * @since 1.0
             */
            do_action('give_after_user_logout');
        } else {
            // Destroy current Give Session
            give()->session->destroy_session();
        }

        return new WP_REST_Response(
            [
                'status' => 200,
                'response' => 'logout_successful',
                'body_response' => [
                    'message' => __('User was logged out successfully.', 'give'),
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
