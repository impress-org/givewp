<?php

namespace Give\Notifications\Routes\DismissibleNotifications;

use Give\API\RestRoute;
use WP_REST_Request;
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
                    'args' => [
                        'type' => [
                            'type' => 'enum',
                            'required' => true,
                            'enum' => ['user', 'system']
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        switch ($request->get_param('type')) {
            case 'user':
                $notifications = get_user_meta(get_current_user_id(), 'give_notifications', true);

                if ( ! is_array($notifications)) {
                    $notifications = [];
                }

                return new WP_REST_Response($notifications, 200);

            case 'system':
                return new WP_REST_Response(
                    give_get_option('give_notifications', []),
                    200
                );

            default:
                return new WP_REST_Response('Invalid notification type', 400);
        }
    }
}
