<?php

namespace Give\NotificationsHandler\Routes;

use Give\API\RestRoute;
use WP_REST_Response;

/**
 * Get notification route
 *
 * @unreleased
 */
class GetNotifications implements RestRoute
{
    protected $endpoint = 'get-notifications';

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
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => 'is_user_logged_in',
                    'args' => [],
                ],
            ]
        );
    }

    /**
     * @unreleased
     */
    public function handleRequest(): WP_REST_Response
    {
        $notifications = get_user_meta(get_current_user_id(), 'give_notifications', true);

        if ( ! is_array($notifications)) {
            $notifications = [];
        }

        return new WP_REST_Response($notifications, 200);
    }
}
