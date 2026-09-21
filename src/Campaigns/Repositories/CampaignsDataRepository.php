<?php

namespace Give\Campaigns\Repositories;

use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;

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
     * Cached stats for the given campaigns. Campaigns missing from the cache are queried and added
     * to it, so a warm cache never hides a campaign that arrived after it was built.
     *
     * @since TBD Query and cache campaigns that are missing from the cache; read subscriptions from the option they are written to.
     * @since 4.8.0 added data caching layer
     *
     * @param int[] $ids
     *
     * @return CampaignsDataRepository
     */
    public static function campaigns(array $ids): CampaignsDataRepository
    {
        $self = new self();
        $emptyCache = ['amounts' => [], 'donationsCount' => [], 'donorsCount' => []];
        $campaignsData = array_merge($emptyCache, (array)get_option('give_campaigns_data', []));
        $campaignsSubscriptionData = array_merge($emptyCache, (array)get_option('give_campaigns_subscriptions_data', []));

        $cachedIds = array_column($campaignsData['donationsCount'], 'campaign_id');
        $uncachedIds = array_values(array_filter($ids, static function ($id) use ($cachedIds) {
            return ! in_array((string)$id, array_map('strval', $cachedIds), true);
        }));

        if ($uncachedIds) {
            $donations = CampaignsDataQuery::donations($uncachedIds);

            $campaignsData = [
                'amounts' => array_merge($campaignsData['amounts'], self::withZeroRows($donations->collectIntendedAmounts(), $uncachedIds, 'sum')),
                'donationsCount' => array_merge($campaignsData['donationsCount'], self::withZeroRows($donations->collectDonations(), $uncachedIds, 'count')),
                'donorsCount' => array_merge($campaignsData['donorsCount'], self::withZeroRows($donations->collectDonors(), $uncachedIds, 'count')),
            ];

            update_option('give_campaigns_data', $campaignsData);

            if (defined('GIVE_RECURRING_VERSION')) {
                $subscriptions = CampaignsDataQuery::subscriptions($uncachedIds);

                $campaignsSubscriptionData = [
                    'amounts' => array_merge($campaignsSubscriptionData['amounts'], self::withZeroRows($subscriptions->collectInitialAmounts(), $uncachedIds, 'sum')),
                    'donationsCount' => array_merge($campaignsSubscriptionData['donationsCount'], self::withZeroRows($subscriptions->collectDonations(), $uncachedIds, 'count')),
                    'donorsCount' => array_merge($campaignsSubscriptionData['donorsCount'], self::withZeroRows($subscriptions->collectDonors(), $uncachedIds, 'count')),
                ];

                update_option('give_campaigns_subscriptions_data', $campaignsSubscriptionData);
            }
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
     * The aggregate queries return no row for a campaign with no donations. Add a zero row for each
     * such campaign so it counts as cached and is not queried again on every page load.
     *
     * @since TBD
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
     * @since 4.0.0
     *
     * Get goal data for campaign
     *
     * @param Campaign $campaign
     *
     * @return array{actual: int, goal: int, actualFormatted: string, goalFormatted:string, percentage:float}
     */
    public function getGoalData(Campaign $campaign): array
    {
        $actual = $this->getActualGoal($campaign);
        $percentage = $campaign->goal
            ? $actual / $campaign->goal
            : 0;

        return [
            'actual' => $actual,
            'goal' => $campaign->goal,
            'actualFormatted' => $campaign->goalType == CampaignGoalType::AMOUNT
                ? give_currency_filter(give_format_amount($actual))
                : $actual,
            'goalFormatted' => $campaign->goalType == CampaignGoalType::AMOUNT
                ? give_currency_filter(give_format_amount($campaign->goal))
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
