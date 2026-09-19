<?php

namespace Give\Donations\Endpoints;

use Closure;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.0.0 replace form with campaignId.
 * @since 3.4.0 The class is extendable
 */
class ListDonations extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donations';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @var DonationsListTable
     */
    protected $listTable;

    /**
     * @since 4.12.0 Updated status parameter to accept multiple comma-separated values
     * @since 4.6.0 add status parameter to filter donations by status
     * @since 3.4.0
     * @access public
     */
    public function __construct(DonationsListTable $listTable)
    {
        $this->listTable = $listTable;
    }

    /**
     * @inheritDoc
     *
     * @since 4.12.0 Add format parameter to start and end dates, replacing custom validation callback
     * @since 3.4.0
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
                    'campaignId' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'start' => [
                        'type' => 'string',
                        'required' => false,
                        'format' => 'date-time'
                    ],
                    'end' => [
                        'type' => 'string',
                        'required' => false,
                        'format' => 'date-time'
                    ],
                    'donor' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
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
                        'type' => 'array',
                        'required' => false,
                        'items' => [
                            'type' => 'string',
                            'enum' => array_values(DonationStatus::toArray()),
                        ],
                        'description' => 'Filter donations by status. Accepts comma-separated list of DonationStatus values (e.g., "pending,publish,trash"). If not provided, excludes trash donations by default.'
                    ],
                ],
            ]
        );
    }

    /**
     * @since 2.24.0 Change this to use the new ListTable class
     * @since      2.20.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     * @throws ColumnIdCollisionException
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;

        $donations = $this->getDonations();
        $donationsCount = $this->getTotalDonationsCount();
        $totalPages = (int)ceil($donationsCount / $this->request->get_param('perPage'));

        if ('model' === $this->request->get_param('return')) {
            $items = $donations;
        } else {
            $this->listTable->items($donations, $this->request->get_param('locale') ?? '');
            $items = $this->listTable->getItems();
        }

        return new WP_REST_Response(
            [
                'items' => $items,
                'totalItems' => $donationsCount,
                'totalPages' => $totalPages,
            ]
        );
    }

    /**
     * @since TBD Select the page of IDs first, then hydrate, so deep pages do not join every row
     * @since 2.24.0 Replace Query Builder with Donations model
     * @since 2.21.0
     *
     * @return array
     */
    public function getDonations(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');
        $sortColumns = $this->listTable->getSortColumnById($this->request->get_param('sortColumn') ?: 'id');
        $sortDirection = $this->request->get_param('sortDirection') ?: 'desc';

        // Resolve the page of IDs against the posts table with only the meta the filters and sort
        // need, then hydrate those rows. Paging the fully joined model query scans every row.
        $idQuery = DB::table('posts')->select(['ID', 'id'], ['post_date', 'createdAt'], ['post_status', 'status']);
        list($idQuery, $dependencies) = $this->getWhereConditions($idQuery);
        $dependencies = array_merge($dependencies, $this->getSortDependencies($sortColumns));

        if ($dependencies) {
            $idQuery->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                ...DonationMetaKeys::getColumnsForAttachMetaQueryFromArray($dependencies)
            );
        }

        foreach ($sortColumns as $sortColumn) {
            $idQuery->orderBy($sortColumn, $sortDirection);
        }

        $ids = array_column($idQuery->limit($perPage)->offset(($page - 1) * $perPage)->getAll() ?: [], 'id');

        if (!$ids) {
            return [];
        }

        $query = give()->donations->prepareQuery()->whereIn('ID', $ids);

        foreach ($sortColumns as $sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return $query->getAll() ?: [];
    }

    /**
     * Meta keys a sort expression references, so the ID query can attach them.
     *
     * @since TBD
     *
     * @param string[] $sortColumns
     *
     * @return DonationMetaKeys[]
     */
    private function getSortDependencies(array $sortColumns): array
    {
        $sortSql = implode(' ', $sortColumns);
        $candidates = [
            DonationMetaKeys::FIRST_NAME(),
            DonationMetaKeys::LAST_NAME(),
            DonationMetaKeys::AMOUNT(),
            DonationMetaKeys::EXCHANGE_RATE(),
            DonationMetaKeys::GATEWAY(),
        ];

        return array_values(array_filter($candidates, static function (DonationMetaKeys $key) use ($sortSql) {
            return strpos($sortSql, $key->getKeyAsCamelCase()) !== false;
        }));
    }

    /**
     * @since TBD Drop the GROUP BY on mode, which made count() return the size of one mode group
     * @since 2.24.0 Replace Query Builder with Donations model
     * @since 2.21.0
     *
     * @return int
     */
    public function getTotalDonationsCount(): int
    {
        list($query, $dependencies) = $this->getWhereConditions(DB::table('posts'));

        if ($dependencies) {
            $query->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                ...DonationMetaKeys::getColumnsForAttachMetaQueryFromArray($dependencies)
            );
        }

        return $query->count();
    }

    /**
     * @since TBD
     */
    private function donationIdsWithNamePrefix(string $value): Closure
    {
        return $this->donationIdsWithMetaPrefix([DonationMetaKeys::FIRST_NAME, DonationMetaKeys::LAST_NAME], $value);
    }

    /**
     * Subquery for donation IDs whose meta value starts with the search term. A prefix match on
     * the (meta_key, meta_value) index replaces a leading-wildcard LIKE across joined meta tables,
     * which had to scan every row for the key.
     *
     * @since TBD
     *
     * @param string[] $metaKeys
     */
    private function donationIdsWithMetaPrefix(array $metaKeys, string $value): Closure
    {
        $prefix = DB::esc_like($value) . '%';

        return static function (QueryBuilder $builder) use ($metaKeys, $prefix) {
            $builder
                ->select('donation_id')
                ->from('give_donationmeta')
                ->whereIn('meta_key', $metaKeys)
                ->where('meta_value', $prefix, 'LIKE');
        };
    }

    /**
     * @since TBD Match name and email searches by prefix through indexed subqueries, and filter test mode the same way instead of HAVING
     * @since 4.12.0 Updated status filtering to accept multiple comma-separated values
     * @since 4.8.0 Added support for subscriptionId parameter to filter donations
     * @since 4.6.0 add status status condition to filter donations
     * @since 3.4.0 Make this method protected so it can be extended
     * @since 3.2.0 Updated query to account for possible null and empty values for _give_payment_mode meta
     * @since      2.24.0 Remove joins as it uses ModelQueryBuilder and change clauses to use attach_meta
     * @since      2.21.0
     *
     * @param QueryBuilder $query
     *
     * @return array{0: QueryBuilder, 1: array<DonationMetaKeys>}
     */
    protected function getWhereConditions(QueryBuilder $query): array
    {
        $search = $this->request->get_param('search');
        $start = $this->request->get_param('start');
        $end = $this->request->get_param('end');
        $donor = $this->request->get_param('donor');
        $testMode = $this->request->get_param('testMode');
        $campaignId = $this->request->get_param('campaignId');
        $subscriptionId = $this->request->get_param('subscriptionId');
        $status = $this->request->get_param('status');
        $dependencies = [];

        $query->where('post_type', 'give_payment');

        if (!empty($status)) {
            $query->whereIn('post_status', $status);
        } else {
            // Default behavior: exclude trash donations
            $query->where('post_status', DonationStatus::TRASH, '<>');
        }

        if ($search) {
            if (ctype_digit($search)) {
                $query->where('id', $search);
            } elseif (strpos($search, '@') !== false) {
                $query->whereIn('ID', $this->donationIdsWithMetaPrefix([DonationMetaKeys::EMAIL], $search));
            } else {
                $query->whereIn('ID', $this->donationIdsWithNamePrefix($search));
            }
        }

        if ($donor) {
            if (ctype_digit($donor)) {
                $query
                    ->where('give_donationmeta_attach_meta_donorId.meta_value', $donor);
                $dependencies[] = DonationMetaKeys::DONOR_ID();
            } else {
                $query->whereIn('ID', $this->donationIdsWithNamePrefix($donor));
            }
        }

        if ($campaignId) {
            $query
                ->where('give_donationmeta_attach_meta_campaignId.meta_value', $campaignId);
            $dependencies[] = DonationMetaKeys::CAMPAIGN_ID();
        }

        if ($subscriptionId) {
            $query
                ->where('give_donationmeta_attach_meta_subscriptionId.meta_value', $subscriptionId);
            $dependencies[] = DonationMetaKeys::SUBSCRIPTION_ID();
        }

        if ($start && $end) {
            $query->whereBetween('post_date', $start, $end);
        } elseif ($start) {
            $query->where('post_date', $start, '>=');
        } elseif ($end) {
            $query->where('post_date', $end, '<=');
        }

        // Test-mode donations carry a mode meta row; live donations may have none. A subquery on
        // (meta_key, meta_value) is index-friendly, unlike a LEFT JOIN with an IS NULL OR condition.
        $testModeDonationIds = static function (QueryBuilder $builder) {
            $builder
                ->select('donation_id')
                ->from('give_donationmeta')
                ->where('meta_key', DonationMetaKeys::MODE)
                ->where('meta_value', DonationMode::TEST);
        };

        if ($testMode) {
            $query->whereIn('ID', $testModeDonationIds);
        } else {
            $query->whereNotIn('ID', $testModeDonationIds);
        }

        return [
            $query,
            $dependencies,
        ];
    }
}
