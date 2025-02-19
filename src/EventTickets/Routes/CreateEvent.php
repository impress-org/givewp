<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\EventTickets\Models\Event;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 3.6.0
 */
class CreateEvent implements RestRoute
{
    /** @var string */
    protected $endpoint = 'events-tickets/events';

    /**
     * @inheritDoc
     *
     * @since 3.20.0 Set the permission callback to "publish_give_payments" and description's sanitize callback to "textarea".
     * @since 3.6.0
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => function () {
                        return current_user_can('edit_give_forms');
                    }
                ],
                'args' => [
                    'title' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'description' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                    'startDateTime' => [
                        'type' => 'string',
                        'format' => 'date-time', // @link https://datatracker.ietf.org/doc/html/rfc3339#section-5.8
                        'required' => true,
                        'validate_callback' => 'rest_parse_date',
                        'sanitize_callback' => function ($value) {
                            return new \DateTime($value);
                        },
                    ],
                    'endDateTime' => [
                        'type' => 'string',
                        'format' => 'date-time', // @link https://datatracker.ietf.org/doc/html/rfc3339#section-5.8
                        'required' => false,
                        'validate_callback' => 'rest_parse_date',
                        'sanitize_callback' => function ($value) {
                            return new \DateTime($value);
                        },
                    ],
                ],
            ]
        );
    }

    /**
     * @since 3.6.0
     *
     * @return WP_REST_Response
     *
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $event = Event::create([
            'title' => $request->get_param('title'),
            'description' => $request->get_param('description'),
            'startDateTime' => $request->get_param('startDateTime'),
            'endDateTime' => $request->get_param('endDateTime'),
        ]);

        return new WP_REST_Response($event->toArray(), 201);
    }
}
