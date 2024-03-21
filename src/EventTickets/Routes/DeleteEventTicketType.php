<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 3.6.0
 */
class DeleteEventTicketType implements RestRoute
{
    /** @var string */
    protected $endpoint = 'events-tickets/ticket-type/(?P<ticket_type_id>\d+)';

    /**
     * @inheritDoc
     *
     * @unreleased Set the permission callback to "delete_give_payments".
     * @since 3.6.0
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => function () {
                        return current_user_can('delete_give_payments');
                    },
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
                ],
            ]
        );
    }

    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $ticketType = EventTicketType::find($request->get_param('ticket_type_id'));

        $salesCount = $ticketType->eventTickets()->count();

        if ($salesCount > 0) {
            return new WP_REST_Response([
                'message' => __('This ticket type has been sold and cannot be deleted.', 'give'),
            ], 400);
        }

        $ticketType->delete();

        return new WP_REST_Response();
    }
}
