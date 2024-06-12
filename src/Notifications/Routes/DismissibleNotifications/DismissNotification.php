<?php

namespace Give\Notifications\Routes\DismissibleNotifications;

use Give\API\RestRoute;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Dismiss notification route
 *
 * @unreleased
 */
class DismissNotification implements RestRoute
{
    protected $endpoint = 'dismiss-notification';

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
                    'permission_callback' => 'is_user_logged_in',
                    'args' => [
                        'notification' => [
                            'type' => 'string',
                            'required' => true,
                        ],
                        'type' => [
                            'type' => 'string',
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

                $notifications[] = $request->get_param('notification');

                update_user_meta(get_current_user_id(), 'give_notifications', $notifications);

                return new WP_REST_Response($notifications, 200);

            case 'system':
                $notifications = give_get_option('give_notifications', []);
                $notifications[] = $request->get_param('notification');

                give_update_option('give_notifications', $notifications);

                return new WP_REST_Response($notifications, 200);

            default:
                return new WP_REST_Response('Invalid notification type', 400);
        }
    }
}
