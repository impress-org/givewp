<?php

namespace Give\Subscriptions\Endpoints;

use DateInterval;
use DateTimeImmutable;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Subscriptions\ListTable\SubscriptionsListTable;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
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
                    'campaignId' => [
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
                    'status' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => [$this, 'validateStatus'],
                        'description' => 'Filter subscriptions by status. Accepts comma-separated list of SubscriptionStatus values (e.g., "active,expired,pending"). If not provided, excludes trash subscriptions by default.'
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
     * @unreleased add sort by donor name
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

        if ('donorName' === $sortColumns[0]) {
            $query->attachMeta(
                'give_donormeta',
                'customer_id',
                'donor_id',
                [DonorMetaKeys::FIRST_NAME, 'firstName'],
                [DonorMetaKeys::LAST_NAME, 'lastName']
            );
            $sortColumns = ['firstName', 'lastName'];
        }

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
     * @unreleased fix search by donor name or email
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
        $start = $this->maybeExpandDate($this->request->get_param('start'), 'before');
        $end = $this->maybeExpandDate($this->request->get_param('end'), 'after');
        $campaignId = $this->request->get_param('campaignId');
        $testMode = $this->request->get_param('testMode');
        $status = $this->request->get_param('status');

        $hasWhereConditions = $search || $start || $end || $campaignId;

        if (!empty($status)) {
            $statuses = array_map('trim', explode(',', $status));
            $query->whereIn('status', $statuses);
        } else {
            // Default behavior: exclude trashed subscriptions
            $query->where('status', SubscriptionStatus::TRASHED, '<>');
        }

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } else {
                $query->whereIn('customer_id', static function (QueryBuilder $builder) use ($search) {
                    $builder
                        ->from('give_donors')
                        ->distinct()
                        ->select('id')
                        ->whereLike('name', $search)
                        ->orWhereLike('email', $search);
                });
            }
        }

        if ($start && $end) {
            $query->whereBetween('created', $start, $end);
        } elseif ($start) {
            $query->where('created', $start, '>=');
        } elseif ($end) {
            $query->where('created', $end, '<=');
        }

        if ($campaignId) {
            $query
                ->whereIn('id', static function (QueryBuilder $builder) use ($campaignId) {
                    $builder
                        ->from('give_donationmeta')
                        ->distinct()
                        ->select('meta_value')
                        ->where('meta_key', DonationMetaKeys::SUBSCRIPTION_ID)
                        ->whereIn('donation_id', static function (QueryBuilder $builder) use ($campaignId) {
                            $builder
                                ->from('give_donationmeta')
                                ->select('donation_id')
                                ->where('meta_key', DonationMetaKeys::CAMPAIGN_ID)
                                ->where('meta_value', $campaignId);
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

    /**
     * @unreleased
     */
    private function maybeExpandDate(?string $date, string $direction = 'before'): ?string
    {
        if (!$this->isValidPeriod($date)) {
            return $date;
        }

        $period = (int) $date;
        $date = new DateTimeImmutable('now', wp_timezone());
        $interval = DateInterval::createFromDateString($direction === 'after' ? "+$period days" : "-$period days");
        $calculatedDate = $date->add($interval);

        return $calculatedDate->format('Y-m-d');
    }
}
