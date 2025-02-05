<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Models\Model;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 3.6.0
 */
class GetEventTicketTypeTickets implements RestRoute
{
    /** @var string */
    protected $endpoint = 'events-tickets/ticket-type/(?P<ticket_type_id>\d+)/tickets';

    /**
     * @inheritDoc
     *
     * @since 3.20.0 Set the permission callback to "read".
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
                        return current_user_can('edit_give_forms');
                    },
                ],
                'args' => [
                    'ticket_type_id' => [
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ($ticketTypeId) {
                            return EventTicketType::find(
                                    $ticketTypeId
                                ) !== null;
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
        $tickets = EventTicket::findByTicketType($request->get_param('ticket_type_id'))
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
                $tickets
            )
        );
    }
}
