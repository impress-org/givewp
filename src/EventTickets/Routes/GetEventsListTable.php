<?php

namespace Give\EventTickets\Routes;

use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Repositories\EventsDataRepository;
use Give\Framework\Database\DB;
use Give\Framework\ListTable\ListCount;
use Give\Framework\Permissions\Facades\UserPermissions;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 3.6.0
 */
class GetEventsListTable
{
    /**
     * @var string
     */
    protected $endpoint = 'events-tickets/events/list-table';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @var EventTicketsListTable
     */
    protected $listTable;

    /**
     * @inheritDoc
     *
     * @since TBD Add the withCount parameter and the count route.
     * @since 4.14.0 update permission capability to use facade
     * @since 3.20.0 Set the permission callback to "read".
     * @since 3.6.0
     */
    public function registerRoute(): void
    {
        $args = [
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
            'locale' => [
                'type' => 'string',
                'required' => false,
                'default' => get_locale(),
            ],
        ];

        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => function () {
                        return UserPermissions::events()->canView();
                    },
                ],
                'args' => $args + ListCount::arguments(),
            ]
        );

        register_rest_route(
            'give-api/v2',
            $this->endpoint . '/count',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleCountRequest'],
                    'permission_callback' => function () {
                        return UserPermissions::events()->canView();
                    },
                ],
                'args' => $args,
            ]
        );
    }

    /**
     * @since TBD Load the ticket sales for the whole page and pass them to the list table, and skip the total when the request sends withCount=false.
     * @since 3.6.0
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;
        $this->listTable = give(EventTicketsListTable::class);

        list($events, $hasNextPage) = ListCount::trim($request, $this->getEvents());

        if ('model' === $this->request->get_param('return')) {
            $items = $events;
        } else {
            $eventIds = array_map(static function (Event $event) {
                return $event->id;
            }, $events);

            $this->listTable
                ->setData(EventsDataRepository::events($eventIds))
                ->items($events, $this->request->get_param('locale') ?? '');
            $items = $this->listTable->getItems();
        }

        return new WP_REST_Response(
            [
                'items' => $items,
            ] + $this->getPaging($hasNextPage)
        );
    }

    /**
     * The total and page count, or only whether another page follows when the request skips the total.
     *
     * @since TBD
     */
    private function getPaging(bool $hasNextPage): array
    {
        if (!ListCount::isRequested($this->request)) {
            return ['hasNextPage' => $hasNextPage];
        }

        return ListCount::totals($this->getTotalEventsCount(), $this->request);
    }

    /**
     * The total for the same filters as the list, without its rows. It reads the total the same
     * way the list does, so the two cannot disagree.
     *
     * @since TBD
     */
    public function handleCountRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;

        return ListCount::response($this->getTotalEventsCount(), $request);
    }

    /**
     * @since TBD Read one more row when the request skips the total.
     * @since 3.6.0
     */
    public function getEvents(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $sortColumns = $this->listTable->getSortColumnById($this->request->get_param('sortColumn') ?: 'id');
        $sortDirection = $this->request->get_param('sortDirection') ?: 'desc';

        $query = give()->events->prepareQuery();
        $query = $this->getWhereConditions($query);

        foreach ($sortColumns as $sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $query->limit(ListCount::limit($this->request))
            ->offset(($page - 1) * $perPage);

        $events = $query->getAll();

        if (!$events) {
            return [];
        }

        return $events;
    }

    /**
     * @since 3.6.0
     */
    public function getTotalEventsCount(): int
    {
        $query = DB::table('give_events');
        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @since 3.6.0
     */
    private function getWhereConditions(QueryBuilder $query): QueryBuilder
    {
        $search = $this->request->get_param('search');

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } else {
                $query->whereLike('title', $search);
                $query->orWhereLike('description', $search);
            }
        }

        return $query;
    }
}
