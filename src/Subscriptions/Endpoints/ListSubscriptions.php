<?php

namespace Give\Subscriptions\Endpoints;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Subscriptions\ListTable\SubscriptionsListTable;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use WP_REST_Request;
use WP_REST_Response;

class ListSubscriptions extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/subscriptions';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @var SubscriptionsListTable
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
                    'donations' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false
                    ],
                    'start' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                    'form' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'end' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
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
                            'desc',
                        ],
                    ],
                    'locale' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => get_locale(),
                    ],
                    'testMode' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => give_is_test_mode(),
                    ],
                    'return' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'columns',
                        'enum' => [
                            'model',
                            'columns',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @since 2.24.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;
        $this->listTable = give(SubscriptionsListTable::class);

        $subscriptions = $this->getSubscriptions();
        $subscriptionsCount = $this->getTotalSubscriptionsCount();
        $pageCount = (int)ceil($subscriptionsCount / $request->get_param('perPage'));

        if ('model' === $this->request->get_param('return')) {
            $items = $subscriptions;
        } else {
            $this->listTable->items($subscriptions, $this->request->get_param('locale') ?? '');
            $items = $this->listTable->getItems();
        }

        return new WP_REST_Response(
            [
                'items' => $items,
                'totalItems' => $subscriptionsCount,
                'totalPages' => $pageCount,
            ]
        );
    }

    /**
     * @since 2.24.0
     *
     * @return array
     */
    public function getSubscriptions(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $sortColumns = $this->listTable->getSortColumnById($this->request->get_param('sortColumn') ?: 'id');
        $sortDirection = $this->request->get_param('sortDirection') ?: 'desc';

        $query = give()->subscriptions->prepareQuery();
        $query = $this->getWhereConditions($query);

        foreach ($sortColumns as $sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        $query->limit($perPage)
            ->offset(($page - 1) * $perPage);

        $subscriptions = $query->getAll();

        if (!$subscriptions) {
            return [];
        }

        return $subscriptions;
    }

    /**
     * @since 2.24.0
     *
     * @return int
     */
    public function getTotalSubscriptionsCount(): int
    {
        $query = DB::table('give_subscriptions')->groupBy('payment_mode');
        $query = $this->getWhereConditions($query);

        return $query->count();
    }

    /**
     * @since 2.24.0 Replace Query Builder with Subscriptions model
     * @since 2.21.0
     *
     * @param QueryBuilder $query
     *
     * @return QueryBuilder
     */
    private function getWhereConditions(QueryBuilder $query): QueryBuilder
    {
        $search = $this->request->get_param('search');
        $start = $this->request->get_param('start');
        $end = $this->request->get_param('end');
        $form = $this->request->get_param('form');
        $testMode = $this->request->get_param('testMode');

        $hasWhereConditions = $search || $start || $end || $form;

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } else {
                $query->whereLike('name', $search);
                $query->orWhereLike('email', $search);
            }
        }

        if ($start && $end) {
            $query->whereBetween('date_created', $start, $end);
        } else if ($start) {
            $query->where('date_created', $start, '>=');
        } else if ($end) {
            $query->where('date_created', $end, '<=');
        }

        if ($form) {
            $query
                ->whereIn('id', static function (QueryBuilder $builder) use ($form) {
                    $builder
                        ->from('give_donationmeta')
                        ->distinct()
                        ->select('meta_value')
                        ->where('meta_key', '_give_payment_subscription_id')
                        ->whereIn('donation_id', static function (QueryBuilder $builder) use ($form) {
                            $builder
                                ->from('give_donationmeta')
                                ->select('donation_id')
                                ->where('meta_key', '_give_payment_form_id')
                                ->where('meta_value', $form);
                        });
                });
        }

        if ($hasWhereConditions) {
            $query->having('payment_mode', '=', $testMode ? SubscriptionMode::TEST : SubscriptionMode::LIVE);
        } else {
            $query->where('payment_mode', $testMode ? SubscriptionMode::TEST : SubscriptionMode::LIVE);
        }

        return $query;
    }
}
