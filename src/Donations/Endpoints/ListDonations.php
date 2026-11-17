<?php

namespace Give\Donations\Endpoints;

use Closure;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\Repositories\DonationCounters;
use Give\Donations\Repositories\DonationTotalsCache;
use Give\Donations\Repositories\RecurringDonationCondition;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\ListTable\ListCount;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\WhereQueryBuilder;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.0.0 replace form with campaignId.
 * @since 3.4.0 The class is extendable
 */
class ListDonations extends Endpoint
{
    /**
     * A request that skips the total reads forward from the start, unless its page begins at this many
     * rows or more: only then does it count first, to read the page from the other end when it can.
     *
     * @since TBD
     */
    private const DEEP_PAGE_OFFSET = 10000;

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
     * The number of donations that match the request, counted live for this request. Only an exact
     * count may choose which rows a page returns; a cached one may be outdated. Null when not counted.
     *
     * @since TBD
     *
     * @var int|null
     */
    protected $totalDonations;

    /**
     * Whether a count runs against the database instead of reading the totals cache.
     *
     * @var bool
     */
    private $countsLive = false;

    /**
     * Whether the displayed total came from the donation counters.
     *
     * @var bool
     */
    private $totalFromCounters = false;

    /**
     * How many IDs a page read forward found, and whether a row follows them. Null until then.
     *
     * @var array{0: int, 1: bool}|null
     */
    private $forwardPage;

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
     * @since TBD Accept a comma list of campaign IDs and a list of form IDs (at most 50 each), and add the donationType parameter, the withCount parameter and the count route.
     * @since 4.19.0 Add formId parameter and accept "none" for campaignId
     * @since 4.12.0 Add format parameter to start and end dates, replacing custom validation callback
     * @since 3.4.0
     */
    public function registerRoute()
    {
        $args = [
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
                'type' => 'string',
                'required' => false,
                'default' => '',
                'pattern' => '^(none|\\d*|\\d+(,\\d+){1,49})$',
                'description' => 'Filter donations by campaign ID or a comma-separated list of up to 50 campaign IDs, or "none" for donations that have no campaign.',
            ],
            'formId' => [
                'type' => 'array',
                'required' => false,
                'default' => [],
                'maxItems' => 50,
                'items' => [
                    'type' => 'integer',
                ],
                'description' => 'Filter donations by one or more form IDs. At most 50.',
            ],
            'donationType' => [
                'type' => 'string',
                'required' => false,
                'enum' => [
                    'one-time',
                    'recurring',
                ],
                'description' => 'Filter donations by type. Recurring donations have a subscription ID.',
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
        ];

        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => $args + ListCount::arguments(),
            ]
        );

        register_rest_route(
            'give-api/v2',
            $this->endpoint . '/count',
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handleCountRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => $args,
            ]
        );
    }

    /**
     * @since TBD Skip the total when the request sends withCount=false, and count live before reading a deep page from the other end, correct a cached or counted total with what the page read found, and mark the donation counters dirty when a live count proves them wrong.
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
        $this->totalDonations = null;
        $this->forwardPage = null;
        $this->totalFromCounters = false;

        // A request that skips the total only counts for a deep page, which needs it to be read from the other end.
        $donationsCount = ListCount::isRequested($request) || $this->isDeepPage() ? $this->getTotalDonationsCount() : null;

        if ($donationsCount !== null && $this->needsLiveCount($donationsCount)) {
            $countedTotal = $this->totalFromCounters ? $donationsCount : null;
            $donationsCount = $this->countLive();
            $this->checkCountersAgainstLiveCount($countedTotal, $donationsCount);
        }

        $donations = $this->getDonations();

        if ($donationsCount !== null) {
            $donationsCount = $this->correctTotal($donationsCount);
        }

        if ('model' === $this->request->get_param('return')) {
            $items = $donations;
        } else {
            $this->listTable->items($donations, $this->request->get_param('locale') ?? '');
            $items = $this->listTable->getItems();
        }

        // A total that was read anyway is sent even when the request skipped it.
        $paging = $donationsCount === null
            ? ['hasNextPage' => $this->forwardPage[1] ?? false]
            : ListCount::totals($donationsCount, $request);

        if ($donationsCount !== null && !ListCount::isRequested($request)) {
            $paging['hasNextPage'] = $this->hasNextPage($donationsCount);
        }

        return new WP_REST_Response(['items' => $items] + $paging);
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
        $this->totalDonations = null;
        $this->forwardPage = null;
        $this->totalFromCounters = false;

        return ListCount::response($this->getTotalDonationsCount(), $request);
    }

    /**
     * Whether the page begins so deep that reading it forward would walk a long way.
     *
     * @since TBD
     */
    private function isDeepPage(): bool
    {
        return ($this->request->get_param('page') - 1) * $this->request->get_param('perPage') >= self::DEEP_PAGE_OFFSET;
    }

    /**
     * Whether rows follow the requested page, once the total is known.
     *
     * @since TBD
     */
    private function hasNextPage(int $total): bool
    {
        return $this->request->get_param('page') * $this->request->get_param('perPage') < $total;
    }

    /**
     * Whether the page needs a live count before its rows are read: it lies past the middle of the
     * estimated total and the list is sorted by ID alone, so it is read from the other end; or it
     * lies past the estimated end, where a forward read would walk every donation to find out
     * whether rows were added. A class that extends this endpoint may count differently, so it
     * always reads forward.
     *
     * @since TBD
     */
    private function needsLiveCount(int $estimatedTotal): bool
    {
        if (static::class !== self::class) {
            return false;
        }

        $sortColumns = $this->listTable->getSortColumnById($this->request->get_param('sortColumn') ?: 'id');
        $offset = ($this->request->get_param('page') - 1) * $this->request->get_param('perPage');

        return $offset >= $estimatedTotal || ($sortColumns === ['id'] && $offset > $estimatedTotal / 2);
    }

    /**
     * Counts the matching donations against the database, past the totals cache, and keeps the
     * count for choosing the page's rows.
     *
     * @since TBD
     */
    private function countLive(): int
    {
        $this->countsLive = true;

        try {
            $this->totalDonations = $this->getTotalDonationsCount();
        } finally {
            $this->countsLive = false;
        }

        return $this->totalDonations;
    }

    /**
     * A live count that differs from the donation counters proves them wrong, unless a write changed
     * the counters while it ran: then the two cannot be compared, and nothing is marked.
     *
     * @since TBD
     */
    private function checkCountersAgainstLiveCount(?int $countedTotal, int $liveTotal): void
    {
        if ($countedTotal === null || $countedTotal === $liveTotal) {
            return;
        }

        if ($this->countFromCounters() === $countedTotal) {
            DonationCounters::markDirty();
        }
    }

    /**
     * A cached total may be outdated. A page read forward is exact, so it bounds the total: a short
     * page ends the list, and a row after a full page means at least one more page. The pager then
     * always reaches the last rows. A total from the donation counters that the page contradicts
     * marks them dirty.
     *
     * @since TBD
     */
    private function correctTotal(int $total): int
    {
        if ($this->totalDonations !== null || $this->forwardPage === null) {
            return $total;
        }

        $corrected = $this->boundTotal($total);

        // The page proves the donation counters wrong: recount them soon.
        if ($corrected !== $total && $this->totalFromCounters) {
            DonationCounters::markDirty();
        }

        return $corrected;
    }

    /**
     * The total bounded by what the forward page read found.
     *
     * @since TBD
     */
    private function boundTotal(int $total): int
    {
        list($found, $hasMore) = $this->forwardPage;
        $offset = ($this->request->get_param('page') - 1) * $this->request->get_param('perPage');

        if ($hasMore) {
            return max($total, $offset + $found + 1);
        }

        if ($found > 0 || $offset === 0) {
            return $offset + $found;
        }

        return min($total, $offset);
    }

    /**
     * @since TBD Skip the query past the last match, and read a page past the middle from the other end when the list is sorted by ID only, both only with a live count; read one row more forward to see whether another page follows.
     * @since 4.18.0 Select the page of IDs first, then hydrate, so deep pages do not join every row
     * @since 2.24.0 Replace Query Builder with Donations model
     * @since 2.21.0
     *
     * @return array
     */
    public function getDonations(): array
    {
        $page = $this->request->get_param('page');
        $perPage = $this->request->get_param('perPage');

        // Past the last match there is nothing to read, and looking would walk every donation.
        if ($this->totalDonations !== null && ($page - 1) * $perPage >= $this->totalDonations) {
            return [];
        }

        $sortColumns = $this->listTable->getSortColumnById($this->request->get_param('sortColumn') ?: 'id');
        $sortDirection = $this->request->get_param('sortDirection') ?: 'desc';

        // Resolve the page of IDs against the posts table with only the meta the filters and sort
        // need, then hydrate those rows. Paging the fully joined model query scans every row.
        $idQuery = DB::table('posts')->select(
            ['ID', 'id'],
            ['post_date', 'createdAt'],
            ['post_modified', 'updatedAt'],
            ['post_status', 'status']
        );
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

        list($offset, $limit, $idSortDirection) = $this->getIdPageWindow($page, $perPage, $sortColumns, $sortDirection);

        foreach ($sortColumns as $sortColumn) {
            $idQuery->orderBy($sortColumn, $idSortDirection);
        }

        $readsForward = $this->totalDonations === null;
        $ids = array_column($idQuery->limit($readsForward ? $limit + 1 : $limit)->offset($offset)->getAll() ?: [], 'id');

        if ($readsForward) {
            $this->forwardPage = [min(count($ids), $limit), count($ids) > $limit];
            $ids = array_slice($ids, 0, $limit);
        }

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
     * The offset, limit and direction that read the requested page of IDs.
     *
     * An offset makes the database walk every skipped row, and each one needs a meta lookup for the
     * filters. IDs are unique, so when the list is sorted by ID alone a page past the middle is the
     * same rows read from the other end with a short offset. The caller re-sorts the rows it hydrates.
     *
     * @since TBD
     *
     * @param string[] $sortColumns
     *
     * @return array{0: int, 1: int, 2: string}
     */
    private function getIdPageWindow(int $page, int $perPage, array $sortColumns, string $sortDirection): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->totalDonations;

        if ($total === null || $sortColumns !== ['id'] || $offset <= $total / 2 || $offset >= $total) {
            return [$offset, $perPage, $sortDirection];
        }

        $limit = min($perPage, $total - $offset);
        $reverseDirection = strtolower($sortDirection) === 'asc' ? 'desc' : 'asc';

        return [$total - $offset - $limit, $limit, $reverseDirection];
    }

    /**
     * Meta keys a sort expression references, so the ID query can attach them.
     *
     * @since 4.18.0
     *
     * @param string[] $sortColumns
     *
     * @return DonationMetaKeys[]
     */
    private function getSortDependencies(array $sortColumns): array
    {
        $sortSql = implode(' ', $sortColumns);

        return array_values(array_filter(DonationMetaKeys::values(), static function (DonationMetaKeys $key) use ($sortSql) {
            return strpos($sortSql, $key->getKeyAsCamelCase()) !== false;
        }));
    }

    /**
     * @since TBD Read the donation counters, or count from the test-mode meta rows, when only post columns and the mode filter apply, and cache the total for display (outdated within the totals cache's max age).
     * @since 4.18.0 Drop the GROUP BY on mode, which made count() return the size of one mode group
     * @since 2.24.0 Replace Query Builder with Donations model
     * @since 2.21.0
     *
     * @return int
     */
    public function getTotalDonationsCount(): int
    {
        if ($this->isFilteredByPostColumnsOnly()) {
            return $this->countByMode();
        }

        list($query, $dependencies) = $this->getWhereConditions(DB::table('posts'));

        if ($dependencies) {
            $query->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                ...DonationMetaKeys::getColumnsForAttachMetaQueryFromArray($dependencies)
            );
        }

        return (int)$this->rememberTotal(__METHOD__ . (clone $query)->getSQL(), static function () use ($query) {
            return $query->count();
        });
    }

    /**
     * A total from the totals cache, which may be outdated within its max age since it is only
     * displayed, or straight from the database while counting live (then cached for display).
     *
     * @since TBD
     *
     * @return mixed
     */
    private function rememberTotal(string $key, callable $callback)
    {
        if ($this->countsLive) {
            return DonationTotalsCache::recompute($key, $callback);
        }

        return DonationTotalsCache::remember($key, $callback, true);
    }

    /**
     * Whether the request filters only on post columns (status and dates) besides the mode. A class
     * that extends this endpoint may add conditions in getWhereConditions(), so it never qualifies.
     *
     * @since TBD
     */
    private function isFilteredByPostColumnsOnly(): bool
    {
        if (static::class !== self::class) {
            return false;
        }

        foreach (['search', 'donor', 'campaignId', 'formId', 'donationType', 'subscriptionId'] as $param) {
            $value = $this->request->get_param($param);

            if ($value !== null && $value !== '' && $value !== []) {
                return false;
            }
        }

        return true;
    }

    /**
     * The total for a request that filters only on post columns besides the mode.
     *
     * The test-mode donations are counted from their mode meta rows, joined to the posts in that order:
     * the rows sit together in the (meta_key, meta_value) index and in the table, so the database reads
     * them in sequence instead of looking up the meta of every donation. A live-mode total is every
     * matching donation minus the test-mode ones, which is what the list's NOT IN condition selects.
     * Without a date range the donation counters answer when they can be used, and the cached
     * value is kept current with them.
     *
     * @since TBD
     */
    private function countByMode(): int
    {
        $testModeQuery = $this->getTestModeCountQuery();

        if ($this->request->get_param('testMode')) {
            $key = __METHOD__ . $testModeQuery->getSQL();
            $count = static function () use ($testModeQuery) {
                return (int)DB::get_var($testModeQuery->getSQL());
            };
        } else {
            $allQuery = DB::table('posts');
            $this->applyPostConditions($allQuery, '');

            $key = __METHOD__ . $testModeQuery->getSQL() . (clone $allQuery)->getSQL();
            $count = static function () use ($testModeQuery, $allQuery) {
                return $allQuery->count() - (int)DB::get_var($testModeQuery->getSQL());
            };
        }

        $counted = $this->countFromCounters();

        if ($counted !== null) {
            // Keep the fallback current, for when the counters are dirty.
            DonationTotalsCache::seed($key, $counted);

            return $counted;
        }

        return (int)$this->rememberTotal($key, $count);
    }

    /**
     * The total from the donation counters, for a request with no date range that does not count
     * live. Null when the counters cannot be used. Only for display: rows are never chosen by it.
     *
     * @since TBD
     */
    private function countFromCounters(): ?int
    {
        if ($this->countsLive || $this->request->get_param('start') || $this->request->get_param('end')) {
            return null;
        }

        $status = $this->request->get_param('status');
        $total = DonationCounters::total((bool)$this->request->get_param('testMode'), empty($status) ? null : (array)$status);
        $this->totalFromCounters = $total !== null;

        return $total;
    }

    /**
     * Counts the test-mode donations that match the post conditions, starting from their mode meta rows.
     *
     * @since TBD
     */
    private function getTestModeCountQuery(): QueryBuilder
    {
        $query = DB::table('give_donationmeta', 'mode')
            ->selectRaw('SELECT STRAIGHT_JOIN COUNT(DISTINCT mode.donation_id) AS count')
            ->join(static function (JoinQueryBuilder $builder) {
                $builder
                    ->innerJoin('posts', 'donation')
                    ->on('donation.ID', 'mode.donation_id');
            })
            ->where('mode.meta_key', DonationMetaKeys::MODE)
            ->where('mode.meta_value', DonationMode::TEST);

        $this->applyPostConditions($query, 'donation.');

        return $query;
    }

    /**
     * Adds the conditions on post columns: the post type, the status and the date range.
     *
     * @since TBD
     *
     * @param string $prefix table alias with a trailing dot, or an empty string
     */
    private function applyPostConditions(QueryBuilder $query, string $prefix): void
    {
        $status = $this->request->get_param('status');
        $start = $this->request->get_param('start');
        $end = $this->request->get_param('end');

        $query->where("{$prefix}post_type", 'give_payment');

        if (!empty($status)) {
            $query->whereIn("{$prefix}post_status", $status);
        } else {
            // Default behavior: exclude trash donations
            $query->where("{$prefix}post_status", DonationStatus::TRASH, '<>');
        }

        if ($start && $end) {
            $query->whereBetween("{$prefix}post_date", $start, $end);
        } elseif ($start) {
            $query->where("{$prefix}post_date", $start, '>=');
        } elseif ($end) {
            $query->where("{$prefix}post_date", $end, '<=');
        }
    }

    /**
     * @since 4.18.0
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
     * @since 4.18.0
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
     * Subquery for donation IDs that belong to a subscription, by the same rule as the stats tiles
     * that link to this filter. It reads only the recurring rows of the (meta_key, meta_value) index.
     *
     * @since TBD
     */
    private function donationIdsWithSubscription(): Closure
    {
        return static function (QueryBuilder $builder) {
            RecurringDonationCondition::apply($builder->select('donation_id')->from('give_donationmeta'));
        };
    }

    /**
     * @since TBD Accept several campaign and form IDs, filter by donationType, and share the post column conditions with the mode count.
     * @since 4.19.0 Filter by formId, and by campaignId "none" for donations that have no campaign.
     * @since 4.18.0 Match name and email searches by prefix through indexed subqueries, and filter test mode the same way instead of HAVING
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
        $donor = $this->request->get_param('donor');
        $testMode = $this->request->get_param('testMode');
        $campaignId = $this->request->get_param('campaignId');
        $formIds = array_values(array_filter(array_map('intval', (array)$this->request->get_param('formId'))));
        $donationType = $this->request->get_param('donationType');
        $subscriptionId = $this->request->get_param('subscriptionId');
        $dependencies = [];

        $this->applyPostConditions($query, '');

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

        if ($campaignId === 'none') {
            $query->where(static function (WhereQueryBuilder $builder) {
                $builder
                    ->whereIsNull('give_donationmeta_attach_meta_campaignId.meta_value')
                    ->orWhereIn('give_donationmeta_attach_meta_campaignId.meta_value', ['', '0']);
            });
            $dependencies[] = DonationMetaKeys::CAMPAIGN_ID();
        } elseif ($campaignId !== null && $campaignId !== '') {
            $campaignIds = array_values(array_filter(array_map('intval', explode(',', (string)$campaignId))));

            if (count($campaignIds) > 1) {
                $query->whereIn('give_donationmeta_attach_meta_campaignId.meta_value', $campaignIds);
                $dependencies[] = DonationMetaKeys::CAMPAIGN_ID();
            } elseif ($campaignIds) {
                $query->where('give_donationmeta_attach_meta_campaignId.meta_value', $campaignIds[0]);
                $dependencies[] = DonationMetaKeys::CAMPAIGN_ID();
            }
        }

        if ($formIds) {
            $query->whereIn('give_donationmeta_attach_meta_formId.meta_value', $formIds);
            $dependencies[] = DonationMetaKeys::FORM_ID();
        }

        if ($donationType === 'recurring') {
            $query->whereIn('ID', $this->donationIdsWithSubscription());
        } elseif ($donationType === 'one-time') {
            $query->whereNotIn('ID', $this->donationIdsWithSubscription());
        }

        if ($subscriptionId) {
            $query
                ->where('give_donationmeta_attach_meta_subscriptionId.meta_value', $subscriptionId);
            $dependencies[] = DonationMetaKeys::SUBSCRIPTION_ID();
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
