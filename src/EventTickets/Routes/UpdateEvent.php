<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\EventTickets\Models\Event;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 3.6.0
 * @since 3.14.0 add permission callback check
 */
class UpdateEvent implements RestRoute
{
    /** @var string */
    protected $endpoint = 'events-tickets/event/(?P<event_id>\d+)';

    /**
     * @inheritDoc
     *
     * @since 3.20.0 Set the permission callback to "edit_give_payments" and description's sanitize callback to "textarea".
     * @since 3.6.0
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options') || current_user_can('edit_give_forms');
                    },
                ],
                'args' => [
                    'event_id' => [
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ($eventId) {
                            return Event::find($eventId) !== null;
                        },
                        'required' => true,
                    ],
                    'title' => [
                        'type' => 'string',
                        'required' => false,
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
                        'required' => false,
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
        $event = Event::find($request->get_param('event_id'));

        foreach (['title', 'description', 'startDateTime', 'endDateTime'] as $param) {
            if ($request->has_param($param)) {
                $event->setAttribute($param, $request->get_param($param));
            }
        }

        $event->save();

        return new WP_REST_Response($event->toArray(), 200);
    }
}
