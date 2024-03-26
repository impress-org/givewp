<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\DonationForms\Models\DonationForm;
use Give\EventTickets\Models\Event;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 3.6.0
 */
class GetEventForms implements RestRoute
{
    /** @var string */
    protected $endpoint = 'events-tickets/event/(?P<event_id>\d+)/forms';

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
                    'permission_callback' => '__return_true',
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
        $eventIdPattern = sprintf('"eventId":%s', $request->get_param('event_id'));

        $forms = DonationForm::query()
            ->whereLike('give_formmeta_attach_meta_fields.meta_value', '%"name":"givewp/event-tickets"%')
            ->where(function($query) use ($eventIdPattern) {
                $query->whereLike('give_formmeta_attach_meta_fields.meta_value', "%$eventIdPattern}%") // When the eventId is the only block attribute.
                    ->orWhereLike('give_formmeta_attach_meta_fields.meta_value', "%$eventIdPattern,%"); // When the eventId is the NOT only block attribute.
            })
            ->paginate(
                $request->get_param('per_page'),
                $request->get_param('page')
            );

        return new WP_REST_Response(array_column($forms->getAll() ?? [], 'id'));
    }
}
