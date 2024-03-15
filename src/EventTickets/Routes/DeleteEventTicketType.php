<?php

namespace Give\EventTickets\Routes;

use Give\API\RestRoute;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_Error;
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
                    'permission_callback' => [$this, 'permissionsCheck'],
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

    /**
     * @since 3.6.0
     *
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        return current_user_can('delete_posts') ?: new WP_Error(
            'rest_forbidden',
            esc_html__("You don't have permission to delete Event Ticket Types", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
    }
}
