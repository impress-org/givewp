<?php

namespace Give\Campaigns\Repositories;

use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Framework\ListTable\Formatting\CountFormatter;
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
     * The cache holds live-mode stats only. The queries filter by the site's payment mode, and the
     * options carry no mode, so test mode reads straight from the database and never writes. (The
     * Delete Test Transactions tool removes donations without firing the hooks that refresh the
     * cache, so a test-mode cache would go stale.)
     *
     * @since TBD Add missing campaigns to the freshly read option, so a finished refresh is not undone.
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
        $useCache = ! give_is_test_mode();
        $campaignsData = $useCache
            ? array_merge($emptyCache, (array)get_option('give_campaigns_data', []))
            : $emptyCache;
        $campaignsSubscriptionData = $useCache
            ? array_merge($emptyCache, (array)get_option('give_campaigns_subscriptions_data', []))
            : $emptyCache;

        $uncachedIds = self::missingIds($campaignsData, $ids);

        if ($uncachedIds) {
            $donations = CampaignsDataQuery::donations($uncachedIds);

            $campaignsData = self::addRows('give_campaigns_data', $campaignsData, [
                'amounts' => self::withZeroRows($donations->collectIntendedAmounts(), $uncachedIds, 'sum'),
                'donationsCount' => self::withZeroRows($donations->collectDonations(), $uncachedIds, 'count'),
                'donorsCount' => self::withZeroRows($donations->collectDonors(), $uncachedIds, 'count'),
            ], $useCache);
        }

        // The subscriptions cache can lag the donations cache, for example when Recurring is activated later
        $uncachedSubscriptionIds = defined('GIVE_RECURRING_VERSION')
            ? self::missingIds($campaignsSubscriptionData, $ids)
            : [];

        if ($uncachedSubscriptionIds) {
            $subscriptions = CampaignsDataQuery::subscriptions($uncachedSubscriptionIds);

            $campaignsSubscriptionData = self::addRows('give_campaigns_subscriptions_data', $campaignsSubscriptionData, [
                'amounts' => self::withZeroRows($subscriptions->collectInitialAmounts(), $uncachedSubscriptionIds, 'sum'),
                'donationsCount' => self::withZeroRows($subscriptions->collectDonations(), $uncachedSubscriptionIds, 'count'),
                'donorsCount' => self::withZeroRows($subscriptions->collectDonors(), $uncachedSubscriptionIds, 'count'),
            ], $useCache);
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
     * Temporary: this cache is replaced by live goals (SOFT-5098), so this method goes away without
     * a deprecation. Not part of the public API.
     *
     * @since TBD
     *
     * @internal
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
     * Adds the rows of newly queried campaigns to the cache and returns the result. In test mode
     * nothing is stored.
     *
     * Just before the write the option is read again, and only campaigns it has no row for are
     * added, so a row a refresh wrote in the meantime is never overwritten.
     *
     * @since TBD
     *
     * @param array<string, array> $cache   the cache as this request read it
     * @param array<string, array> $newRows rows by cache key, for campaigns that were missing
     *
     * @return array<string, array>
     */
    private static function addRows(string $option, array $cache, array $newRows, bool $store): array
    {
        if (!$store) {
            return self::withNewRows($cache, $newRows);
        }

        // Past the copy WordPress keeps for this request, so a refresh from another request shows.
        wp_cache_delete($option, 'options');

        $merged = self::withNewRows((array)get_option($option, []), $newRows);

        update_option($option, $merged, false);

        return $merged;
    }

    /**
     * The cache with the rows of the campaigns it has no row for added.
     *
     * @since TBD
     *
     * @param array<string, array> $newRows rows by cache key
     *
     * @return array<string, array>
     */
    private static function withNewRows(array $cache, array $newRows): array
    {
        $cache = array_merge(['amounts' => [], 'donationsCount' => [], 'donorsCount' => []], $cache);
        $cachedIds = array_map('strval', array_column($cache['donationsCount'], 'campaign_id'));

        foreach ($newRows as $key => $rows) {
            foreach ($rows as $row) {
                if (!in_array((string)$row['campaign_id'], $cachedIds, true)) {
                    $cache[$key][] = $row;
                }
            }
        }

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
     * @since TBD Add $useListTableFormat to write the amounts with the shared money formatter, and the counts in the admin's locale.
     * @since 4.0.0
     *
     * Get goal data for campaign
     *
     * @param Campaign $campaign
     * @param bool $useListTableFormat The REST API keeps its own format (false); the list table passes true.
     * @param string $locale The browser's language, for the list table counts.
     *
     * @return array{actual: int, goal: int, actualFormatted: string, goalFormatted:string, percentage:float}
     */
    public function getGoalData(Campaign $campaign, bool $useListTableFormat = false, string $locale = ''): array
    {
        $actual = $this->getActualGoal($campaign);
        // The list table also writes recurring amounts as money. The REST API keeps its older output.
        $isAmount = $campaign->goalType == CampaignGoalType::AMOUNT()
            || ($useListTableFormat && $campaign->goalType == CampaignGoalType::AMOUNT_FROM_SUBSCRIPTIONS());
        $formatMoney = $useListTableFormat
            ? static function ($amount): string {
                return MoneyFormatter::fromDecimal($amount);
            }
            : static function ($amount): string {
                return give_currency_filter(give_format_amount($amount));
            };
        // Counts stay numbers for the REST API. Only the list table gets text for the admin's locale.
        $formatCount = static function ($count) use ($useListTableFormat, $locale) {
            return $useListTableFormat ? CountFormatter::format($count, $locale) : $count;
        };
        $percentage = $campaign->goal
            ? $actual / $campaign->goal
            : 0;

        return [
            'actual' => $actual,
            'goal' => $campaign->goal,
            'actualFormatted' => $isAmount
                ? $formatMoney($actual)
                : $formatCount($actual),
            'goalFormatted' => $isAmount
                ? $formatMoney($campaign->goal)
                : $formatCount($campaign->goal),
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
