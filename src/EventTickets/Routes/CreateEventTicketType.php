<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\EventTickets\DataTransferObjects\EventTicketTypeData;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Support\ValueObjects\Money;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 3.6.0
 */
class CreateEventTicketType implements RestRoute
{
    /** @var string */
    protected $endpoint = 'events-tickets/event/(?P<event_id>\d+)/ticket-types';

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
                    'permission_callback' => function () {
                        return current_user_can( 'manage_options' );
                    }
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
                    'title' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'description' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'price' => [
                        'type' => 'integer',
                        'required' => true,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_is_integer',
                        'description' => 'This price to purchase a ticket in the minor amount of the currency. For example, 1000 for $10.00.',
                    ],
                    'capacity' => [
                        'type' => 'integer',
                        'required' => true,
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
        $ticketType = EventTicketType::create([
            'eventId' => $request->get_param('event_id'),
            'title' => $request->get_param('title'),
            'description' => $request->get_param('description'),
            'price' => new Money($request->get_param('price'), give_get_currency()),
            'capacity' => $request->get_param('capacity'),
        ]);

        return new WP_REST_Response(EventTicketTypeData::make($ticketType), 201);
    }
}
