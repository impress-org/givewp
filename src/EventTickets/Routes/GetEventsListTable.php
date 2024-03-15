<?php

namespace Give\EventTickets\Routes;

use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\Framework\Database\DB;
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
     * @since 3.6.0
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
                ],
            ]
        );
    }

    /**
     * @since 3.6.0
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

        $query->limit($perPage)
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

        /**
     * @since 3.6.0
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
