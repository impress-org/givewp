<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Models\Model;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 3.6.0
 */
class GetEventTicketTypes implements RestRoute
{
    /** @var string */
    protected $endpoint = 'events-tickets/event/(?P<event_id>\d+)/ticket-types';

    /**
     * @inheritDoc
     *
     * @unreleased Set the permission callback to "read".
     * @since 3.6.0
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
                    'permission_callback' => function () {
                        return current_user_can('read');
                    },
                ],
                'args' => [
                    'event_id' => [
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ($eventId) {
                            return Event::find($eventId);
                        },
                        'required' => true,
                    ],
                    'page' => [
                        'validate_callback' => function ($param) {
                            return filter_var($param, FILTER_VALIDATE_INT);
                        },
                        'default' => 1,
                    ],
                    'per_page' => [
                        'validate_callback' => function ($param) {
                            return filter_var($param, FILTER_VALIDATE_INT);
                        },
                        'default' => 10,
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
        $ticketTypes = EventTicketType::findByEvent($request->get_param('event_id'))
            ->paginate(
                $request->get_param('page'),
                $request->get_param('per_page')
            )
            ->getAll();

        return new WP_REST_Response(
            array_map(
                function (Model $model) {
                    return $model->toArray();
                },
                $ticketTypes
            )
        );
    }
}
