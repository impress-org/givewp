<?php

namespace Give\Campaigns\Repositories;

use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Donations\ValueObjects\DonationMode;
use Give\Framework\Database\DB;
use Give\Framework\ListTable\Formatting\MoneyFormatter;

/**
 * Used to optimize the campaigns list table performance and to avoid n+1 problems.
 * Instead of doing expensive queries in multiple columns in each row, this class loads everything upfront for multiple campaigns.
 *
 * @since 4.0.0
 */
class CampaignsDataRepository
{
    /**
     * @var array
     */
    private $amounts;
    /**
     * @var array
     */
    private $subscriptionAmounts = [];
    /**
     * @var array
     */
    private $donationsCount;
    /**
     * @var array
     */
    private $subscriptionDonationsCount = [];
    /**
     * @var array
     */
    private $donorsCount;
    /**
     * @var array
     */
    private $subscriptionDonorsCount = [];
    /**
     * @var string[] the campaign ids this instance was asked for
     */
    private $requestedIds = [];

    /**
     * Cached stats for the given campaigns. Campaigns missing from the cache are queried and added
     * to it, so a warm cache never hides a campaign that arrived after it was built.
     *
     * The queries filter by the site's payment mode, so each mode has its own options.
     *
     * @since TBD Cache test-mode stats in their own options instead of querying them on every request, and add missing campaigns to the freshly read option so a finished refresh is not undone.
     * @since 4.18.0 Query and cache campaigns that are missing from the cache; read subscriptions from the option they are written to; bypass the cache in test mode.
     * @since 4.8.0 added data caching layer
     *
     * @param int[] $ids
     *
     * @return CampaignsDataRepository
     */
    public static function campaigns(array $ids): CampaignsDataRepository
    {
        $self = new self();
        $self->requestedIds = array_map('strval', $ids);
        $emptyCache = ['amounts' => [], 'donationsCount' => [], 'donorsCount' => []];
        $mode = give_is_test_mode() ? DonationMode::TEST : DonationMode::LIVE;
        $campaignsData = array_merge($emptyCache, (array)get_option(self::donationsOption($mode), []));
        $campaignsSubscriptionData = array_merge($emptyCache, (array)get_option(self::subscriptionsOption($mode), []));

        $uncachedIds = self::missingIds($campaignsData, $ids);

        if ($uncachedIds) {
            $donations = CampaignsDataQuery::donations($uncachedIds);

            $campaignsData = self::store(self::donationsOption($mode), [
                'amounts' => self::withZeroRows($donations->collectIntendedAmounts(), $uncachedIds, 'sum'),
                'donationsCount' => self::withZeroRows($donations->collectDonations(), $uncachedIds, 'count'),
                'donorsCount' => self::withZeroRows($donations->collectDonors(), $uncachedIds, 'count'),
            ]);
        }

        // The subscriptions cache can lag the donations cache, for example when Recurring is activated later
        $uncachedSubscriptionIds = defined('GIVE_RECURRING_VERSION')
            ? self::missingIds($campaignsSubscriptionData, $ids)
            : [];

        if ($uncachedSubscriptionIds) {
            $subscriptions = CampaignsDataQuery::subscriptions($uncachedSubscriptionIds);

            $campaignsSubscriptionData = self::store(self::subscriptionsOption($mode), [
                'amounts' => self::withZeroRows($subscriptions->collectInitialAmounts(), $uncachedSubscriptionIds, 'sum'),
                'donationsCount' => self::withZeroRows($subscriptions->collectDonations(), $uncachedSubscriptionIds, 'count'),
                'donorsCount' => self::withZeroRows($subscriptions->collectDonors(), $uncachedSubscriptionIds, 'count'),
            ]);
        }

        $self->amounts = $campaignsData['amounts'];
        $self->donationsCount = $campaignsData['donationsCount'];
        $self->donorsCount = $campaignsData['donorsCount'];

        if (defined('GIVE_RECURRING_VERSION')) {
            $self->subscriptionAmounts = $campaignsSubscriptionData['amounts'];
            $self->subscriptionDonationsCount = $campaignsSubscriptionData['donationsCount'];
            $self->subscriptionDonorsCount = $campaignsSubscriptionData['donorsCount'];
        }

        return $self;
    }

    /**
     * The stats rows of the requested campaigns, in the shape the CampaignsDataQuery collect methods return them.
     *
     * @since TBD
     *
     * @return array<string, array>
     */
    public function getRows(): array
    {
        return array_map([$this, 'onlyRequested'], [
            'amounts' => $this->amounts,
            'donationsCount' => $this->donationsCount,
            'donorsCount' => $this->donorsCount,
            'subscriptionAmounts' => $this->subscriptionAmounts,
            'subscriptionDonationsCount' => $this->subscriptionDonationsCount,
            'subscriptionDonorsCount' => $this->subscriptionDonorsCount,
        ]);
    }

    /**
     * The rows of the requested campaigns, so a reader does not scan every cached campaign.
     *
     * @since TBD
     */
    private function onlyRequested(array $rows): array
    {
        return array_values(array_filter($rows, function ($row) {
            return isset($row['campaign_id']) && in_array((string)$row['campaign_id'], $this->requestedIds, true);
        }));
    }

    /**
     * The option that caches the donation stats of a payment mode.
     *
     * @since TBD
     */
    public static function donationsOption(string $mode): string
    {
        return $mode === DonationMode::TEST ? 'give_campaigns_data_test' : 'give_campaigns_data';
    }

    /**
     * The option that caches the subscription stats of a payment mode.
     *
     * @since TBD
     */
    public static function subscriptionsOption(string $mode): string
    {
        return $mode === DonationMode::TEST ? 'give_campaigns_subscriptions_data_test' : 'give_campaigns_subscriptions_data';
    }

    /**
     * Adds the rows of newly queried campaigns to the stored cache and returns the result.
     *
     * The stored option is read again from the database just before the write, and only campaigns
     * it has no row for are added. Writing back the copy read at the start of the request could undo
     * a refresh that finished while the missing campaigns were queried.
     *
     * @since TBD
     *
     * @param array<string, array> $newRows rows by cache key, for campaigns that were missing
     *
     * @return array<string, array>
     */
    private static function store(string $option, array $newRows): array
    {
        global $wpdb;

        $stored = DB::get_var(DB::prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $option));
        $cache = array_merge(
            ['amounts' => [], 'donationsCount' => [], 'donorsCount' => []],
            $stored === null ? [] : (array)maybe_unserialize($stored)
        );
        $cachedIds = array_map('strval', array_column($cache['donationsCount'], 'campaign_id'));

        foreach ($newRows as $key => $rows) {
            foreach ($rows as $row) {
                if (!in_array((string)$row['campaign_id'], $cachedIds, true)) {
                    $cache[$key][] = $row;
                }
            }
        }

        update_option($option, $cache);

        return $cache;
    }

    /**
     * Ids with no row in the given cache.
     *
     * @since 4.18.0
     */
    private static function missingIds(array $cache, array $ids): array
    {
        $cachedIds = array_map('strval', array_column($cache['donationsCount'], 'campaign_id'));

        return array_values(array_filter($ids, static function ($id) use ($cachedIds) {
            return ! in_array((string)$id, $cachedIds, true);
        }));
    }

    /**
     * The aggregate queries return no row for a campaign with no donations. Add a zero row for each
     * such campaign so it counts as cached and is not queried again on every page load.
     *
     * @since 4.18.0
     */
    private static function withZeroRows($rows, array $ids, string $column): array
    {
        $rows = is_array($rows) ? $rows : [];
        $present = array_map('strval', array_column($rows, 'campaign_id'));

        foreach ($ids as $id) {
            if ( ! in_array((string)$id, $present, true)) {
                $rows[] = ['campaign_id' => (string)$id, $column => 0];
            }
        }

        return $rows;
    }

    /**
     * @since 4.2.0 return type of float
     * @since 4.0.0
     *
     * Get revenue for campaign
     *
     * @param Campaign $campaign
     */
    public function getRevenue(Campaign $campaign): float
    {
        $data = $campaign->goalType->isSubscriptions()
            ? $this->subscriptionAmounts
            : $this->amounts;

        foreach ($data as $row) {
            if (isset($row['campaign_id']) && $row['campaign_id'] == $campaign->id) {
                return $row['sum'];
            }
        }

        return 0;
    }

    /**
     * @since 4.0.0
     *
     * Get donations count for campaign
     *
     * @param Campaign $campaign
     *
     * @return int
     */
    public function getDonationsCount(Campaign $campaign): int
    {
        $data = $campaign->goalType->isSubscriptions()
            ? $this->subscriptionDonationsCount
            : $this->donationsCount;

        foreach ($data as $row) {
            if (isset($row['campaign_id']) && $row['campaign_id'] == $campaign->id) {
                return (int)$row['count'];
            }
        }

        return 0;
    }

    /**
     * @since 4.0.0
     *
     * Get donors count for campaign
     *
     * @param Campaign $campaign
     *
     * @return int
     */
    public function getDonorsCount(Campaign $campaign): int
    {
        $data = $campaign->goalType->isSubscriptions()
            ? $this->subscriptionDonorsCount
            : $this->donorsCount;

        foreach ($data as $row) {
            if (isset($row['campaign_id']) && $row['campaign_id'] == $campaign->id) {
                return (int)$row['count'];
            }
        }

        return 0;
    }


    /**
     * @since TBD Add $useListTableFormat to write the amounts with the shared money formatter.
     * @since 4.0.0
     *
     * Get goal data for campaign
     *
     * @param Campaign $campaign
     * @param bool $useListTableFormat The REST API keeps its own format (false); the list table passes true.
     *
     * @return array{actual: int, goal: int, actualFormatted: string, goalFormatted:string, percentage:float}
     */
    public function getGoalData(Campaign $campaign, bool $useListTableFormat = false): array
    {
        $actual = $this->getActualGoal($campaign);
        $formatMoney = $useListTableFormat
            ? static function ($amount): string {
                return MoneyFormatter::fromDecimal($amount);
            }
            : static function ($amount): string {
                return give_currency_filter(give_format_amount($amount));
            };
        $percentage = $campaign->goal
            ? $actual / $campaign->goal
            : 0;

        return [
            'actual' => $actual,
            'goal' => $campaign->goal,
            'actualFormatted' => $campaign->goalType == CampaignGoalType::AMOUNT
                ? $formatMoney($actual)
                : $actual,
            'goalFormatted' => $campaign->goalType == CampaignGoalType::AMOUNT
                ? $formatMoney($campaign->goal)
                : $campaign->goal,
            'percentage' => round($percentage * 100, 2),
        ];
    }

    /**
     * @since 4.2.0 return union type int|float
     * @since 4.0.0
     *
     * @param Campaign $campaign
     *
     * @return int|float
     */
    private function getActualGoal(Campaign $campaign)
    {
        switch ($campaign->goalType->getValue()) {
            case CampaignGoalType::DONATIONS():
            case CampaignGoalType::SUBSCRIPTIONS():
                return $this->getDonationsCount($campaign);
            case CampaignGoalType::DONORS():
            case CampaignGoalType::DONORS_FROM_SUBSCRIPTIONS():
                return $this->getDonorsCount($campaign);
            default:
                return $this->getRevenue($campaign);
        }
    }
}
