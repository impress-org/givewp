<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\EventTickets\DataTransferObjects\EventTicketTypeData;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Support\ValueObjects\Money;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 3.6.0
 */
class UpdateEventTicketType implements RestRoute
{
    /** @var string */
    protected $endpoint = 'events-tickets/ticket-type/(?P<ticket_type_id>\d+)';

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
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'ticket_type_id' => [
                        'type' => 'integer',
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ($eventId) {
                            return EventTicketType::find($eventId);
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
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'price' => [
                        'type' => 'integer',
                        'required' => false,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_is_integer',
                        'description' => 'This price to purchase a ticket in the minor amount of the currency. For example, 1000 for $10.00.',
                    ],
                    'capacity' => [
                        'type' => 'integer',
                        'required' => false,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_is_integer',
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
        $ticketType = EventTicketType::find($request->get_param('ticket_type_id'));

        foreach(['title', 'description', 'capacity'] as $param) {
            if ($request->has_param($param)) {
                $ticketType->setAttribute($param, $request->get_param($param));
            }
        }

        if ($request->has_param('price')) {
            $ticketType->setAttribute('price', new Money($request->get_param('price'), give_get_currency()));
        }

        $ticketType->save();

        return new WP_REST_Response(EventTicketTypeData::make($ticketType)->toArray(), 200);
    }
}
