<?php

namespace Give\Donations\Endpoints;

use Give\Donations\Repositories\DonationTotalsCache;
use Give\Donations\Repositories\RecurringDonationCondition;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\ListTable\Formatting\StatsFormatter;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 4.10.0
 */
class ListDonationsStats extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donations/stats';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @since 4.10.0
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
                    'testMode' => [
                        'type' => 'boolean',
                        'required' => false,
                        'default' => give_is_test_mode(),
                    ],
                ],
            ]
        );
    }

    /**
     * @since TBD Add formatted values.
     * @since 4.10.0
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $this->request = $request;

        $statistics = $this->getDonationStatistics();

        return new WP_REST_Response(StatsFormatter::withFormatted($statistics));
    }

    /**
     * The total, one-time and recurring donation counts for a payment mode.
     *
     * Every count starts from an index: all donations from the posts' type and status index, the
     * test-mode ones from their mode meta rows and the recurring ones from their subscription meta
     * rows, each joined to the posts in that order. The live-mode counts are the whole minus the
     * test-mode part, and one-time is the total minus recurring, so the three always add up. A LEFT
     * JOIN of both meta rows onto every donation read the meta of each one, a minute or more at a
     * million donations.
     *
     * @since TBD Count from the indexes instead of joining the meta of every donation, and count a donation without a subscription row as one-time; allow an outdated value within the totals cache's max age.
     * @since 4.10.0
     */
    public function getDonationStatistics(): array
    {
        $testMode = (bool)$this->request->get_param('testMode');
        $queries = [$this->getTestModeCountQuery(), $this->getRecurringCountQuery(true)];

        if (!$testMode) {
            array_push($queries, $this->getAllCountQuery(), $this->getRecurringCountQuery(false));
        }

        $key = __METHOD__ . implode(';', array_map(static function (QueryBuilder $query) {
            return $query->getSQL();
        }, $queries));

        // Each count still reads one meta row per matching donation. The tiles only display them, so
        // an outdated value may be shown within the totals cache's max age.
        return DonationTotalsCache::remember($key, static function () use ($testMode, $queries) {
            $counts = array_map(static function (QueryBuilder $query) {
                return (int)DB::get_var($query->getSQL());
            }, $queries);

            if ($testMode) {
                list($testModeCount, $recurringTestModeCount) = $counts;

                return self::toStatistics($testModeCount, $recurringTestModeCount);
            }

            list($testModeCount, $recurringTestModeCount, $allCount, $recurringCount) = $counts;

            return self::toStatistics($allCount - $testModeCount, $recurringCount - $recurringTestModeCount);
        }, true);
    }

    /**
     * @since TBD
     */
    private static function toStatistics(int $total, int $recurring): array
    {
        return [
            'donationsCount' => $total,
            'oneTimeDonationsCount' => $total - $recurring,
            'recurringDonationsCount' => $recurring,
        ];
    }

    /**
     * Counts every donation that is not in the trash, from the posts' type and status index.
     *
     * @since TBD
     */
    private function getAllCountQuery(): QueryBuilder
    {
        return $this->wherePostIsDonation(DB::table('posts')->selectRaw('SELECT COUNT(*)'), '');
    }

    /**
     * Counts the test-mode donations, starting from their mode meta rows.
     *
     * @since TBD
     */
    private function getTestModeCountQuery(): QueryBuilder
    {
        $query = DB::table('give_donationmeta', 'mode')
            ->selectRaw('SELECT STRAIGHT_JOIN COUNT(DISTINCT mode.donation_id)')
            ->join(static function (JoinQueryBuilder $builder) {
                $builder
                    ->innerJoin('posts', 'donation')
                    ->on('donation.ID', 'mode.donation_id');
            })
            ->where('mode.meta_key', DonationMetaKeys::MODE)
            ->where('mode.meta_value', DonationMode::TEST);

        return $this->wherePostIsDonation($query, 'donation.');
    }

    /**
     * Counts the recurring donations, starting from their subscription meta rows. Only the
     * test-mode ones when asked, through their mode rows: then the join order is left to the
     * database, which starts from whichever set of rows is smaller (few test donations on a live
     * site, few recurring ones on a test site).
     *
     * @since TBD
     */
    private function getRecurringCountQuery(bool $testModeOnly): QueryBuilder
    {
        $select = $testModeOnly ? 'SELECT' : 'SELECT STRAIGHT_JOIN';
        $query = DB::table('give_donationmeta', 'subscription')
            ->selectRaw("{$select} COUNT(DISTINCT subscription.donation_id)")
            ->join(static function (JoinQueryBuilder $builder) {
                $builder
                    ->innerJoin('posts', 'donation')
                    ->on('donation.ID', 'subscription.donation_id');
            });

        if ($testModeOnly) {
            $query->join(static function (JoinQueryBuilder $builder) {
                $builder
                    ->innerJoin('give_donationmeta', 'mode')
                    ->on('mode.donation_id', 'subscription.donation_id')
                    ->andOn('mode.meta_key', DonationMetaKeys::MODE, true)
                    ->andOn('mode.meta_value', DonationMode::TEST, true);
            });
        }

        RecurringDonationCondition::apply($query, 'subscription.');

        return $this->wherePostIsDonation($query, 'donation.');
    }

    /**
     * @since TBD
     *
     * @param string $prefix table alias with a trailing dot, or an empty string
     */
    private function wherePostIsDonation(QueryBuilder $query, string $prefix): QueryBuilder
    {
        return $query
            ->where("{$prefix}post_type", 'give_payment')
            ->where("{$prefix}post_status", DonationStatus::TRASH, '<>');
    }
}
