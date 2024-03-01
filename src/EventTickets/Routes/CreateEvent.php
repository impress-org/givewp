<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Support\ValueObjects\Money;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class CreateEvent implements RestRoute
{
    /** @var string */
    protected $endpoint = 'events-tickets/events';

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
     * @unreleased
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

        EventTicketType::create([
            'title' => 'General Admission',
            'price' => new Money(1000, give_get_currency()),
            'capacity' => 100,
            'eventId' => $event->id,
        ]);

        return new WP_REST_Response($event->toArray(), 201);
    }
}
