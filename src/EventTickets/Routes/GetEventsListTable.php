<?php

namespace Give\EventTickets\Routes;

use Give\EventTickets\Models\Event;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class GetEventsListTable
{
    /**
     * @var string
     */
    protected $endpoint = 'events-tickets/events/list-table';

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
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1
                    ],
                    'perPage' => [
                        'type' => 'integer',
                        'default' => 30,
                        'minimum' => 1
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'sortColumn' => [
                        'type' => 'string',
                        'default' => 'id',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'sortDirection' => [
                        'type' => 'string',
                        'default' => 'asc',
                        'enum' => ['asc', 'desc'],
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $query = Event::query()
            ->orderBy(
                $request->get_param('sortColumn') ?: 'id',
                $request->get_param('sortDirection') ?: 'asc'
            );

        if($search = $request->get_param('search')) {
            $query
                ->whereLike('title', "%$search%")
                ->orWhereLike('description', "%$search%");
        }

        $events = $query
            ->paginate(
                $request->get_param('perPage'),
                $request->get_param('page')
            );

        return new WP_REST_Response(
            [
                'items' => array_map(
                    [$this, 'transformEventToRow'],
                    $events->getAll() ?: []
                ),
                'totalItems' => $count = $events->count(),
                'totalPages' => ceil($count / $request->get_param('perPage')),
            ]
        );
    }

    /**
     * @unreleased
     */
    protected function transformEventToRow(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $this->formatColumnTitle($event),
            'description' => $event->description,
            'startDate' => $event->start_datetime->format('m/d/Y g:i a'),
            'ticketsSold' => $event->eventTickets()->count(),
        ];
    }

    /**
     * @unreleased
     */
    protected function formatColumnTitle(Event $event): string
    {
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-events&view=overview&id=$event->id"),
            __('View event details', 'give'),
            $event->title
        );
    }

    /**
     * @unreleased
     *
     * @return bool|\WP_Error
     */
    public function permissionsCheck()
    {
        return current_user_can('edit_posts')?: new \WP_Error(
            'rest_forbidden',
            esc_html__("You don't have permission to view Events", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
    }
}
