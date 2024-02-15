<?php

namespace Give\EventTickets\Endpoints;

use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;
use WP_REST_Response;

class ListEvents extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/event-tickets';

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
                        'required' => false,
                        'default' => 1,
                        'minimum' => 1
                    ],
                    'perPage' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 30,
                        'minimum' => 1
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false
                    ],
                    'sortColumn' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'sortDirection' => [
                        'type' => 'string',
                        'required' => false,
                        'enum' => [
                            'asc',
                            'desc'
                        ],
                    ],
                    'locale' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => get_locale(),
                    ],
                    'return' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'columns',
                        'enum' => [
                            'model',
                            'columns'
                        ],
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
        $this->listTable = give(EventTicketsListTable::class);

        $events = $this->getEvents();
        $eventsCount = $this->getTotalEventsCount();
        $pageCount = (int)ceil($eventsCount / $request->get_param('perPage'));

        if ('model' === $this->request->get_param('return')) {
            $items = $events;
        } else {
            $this->listTable->items($events, $this->request->get_param('locale') ?? '');
            $items = $this->listTable->getItems();
        }

        return new WP_REST_Response(
            [
                'items' => $items,
                'totalItems' => $eventsCount,
                'totalPages' => $pageCount
            ]
        );
    }

    /**
     * @unreleased
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

        $query->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $events = $query->getAll();

        if (!$events) {
            return [];
        }

        return $events;
    }

    /**
     * @unreleased
     */
    public function getTotalEventsCount(): int
    {
        $query = DB::table('give_events');
        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @unreleased
     */
    private function getWhereConditions(QueryBuilder $query): QueryBuilder
    {
        $search = $this->request->get_param('search');

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } else {
                $query->whereLike('name', $search);
                $query->orWhereLike('email', $search);
            }
        }

        return $query;
    }
}
