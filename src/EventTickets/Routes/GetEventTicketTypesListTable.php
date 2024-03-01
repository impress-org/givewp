<?php

namespace Give\EventTickets\Routes;

use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetEventTicketTypesListTable
{
    /**
     * @var string
     */
    protected $endpoint = 'events-tickets/event/(?P<event_id>\d+)/ticket-types/list-table';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function registerRoute(): void
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
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
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1
                    ],
                    'perPage' => [
                        'type' => 'integer',
                        'default' => 30,
                        'minimum' => 1
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
                    'locale' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => get_locale(),
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;

        $eventTicketTypes = $this->getEventTicketTypes();
        $eventTicketTypesCount = $this->getTotalEventTicketTypesCount();
        $pageCount = (int)ceil($eventTicketTypesCount / $request->get_param('perPage'));

        $items = $this->prepareItems($eventTicketTypes, $this->request->get_param('locale') ?? '');

        return new WP_REST_Response(
            [
                'items' => $items,
                'totalItems' => $eventTicketTypesCount,
                'totalPages' => $pageCount
            ]
        );
    }

    /**
     * @unreleased
     */
    public function getEventTicketTypes(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $sortColumns = ['id'];
        $sortDirection = $this->request->get_param('sortDirection') ?: 'desc';

        $query = EventTicketType::findByEvent($this->request->get_param('event_id'));

        foreach ($sortColumns as $sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $query->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $eventTicketTypes = $query->getAll();

        if (!$eventTicketTypes) {
            return [];
        }

        return $eventTicketTypes;
    }

    /**
     * @unreleased
     */
    public function getTotalEventTicketTypesCount(): int
    {
        $query = EventTicketType::findByEvent($this->request->get_param('event_id'));

        return $query->count();
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

    /**
     * @unreleased
     */
    private function prepareItems(array $eventTicketTypes, $locale): array
    {
        return array_map(
            function (EventTicketType $eventTicketType) use ($locale) {
                return $this->formatColumns($eventTicketType, $locale);
            },
            $eventTicketTypes
        );
    }

    private function formatColumns(EventTicketType $eventTicketType, string $locale): array
    {
        $soldTicketsCount = $eventTicketType->eventTickets()->count() ?? 0;
        $countString = sprintf(
            __('%1$d of %2$d', 'give'),
            $soldTicketsCount,
            $eventTicketType->capacity
        );

        return [
            'id' => $eventTicketType->id,
            'title' => $eventTicketType->title,
            'count' => $countString,
            'price' => $eventTicketType->price->formatToLocale($locale),
        ];
    }
}
