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
     *
     * @since 4.8.0 added data caching layer
     *
     * @param int[] $ids
     *
     * @return CampaignsDataRepository
     */
    public static function campaigns(array $ids): CampaignsDataRepository
    {
        $self = new self();
        $campaignsData = get_option('give_campaigns_data', []);
        $campaignsSubscriptionData = get_option('give_campaigns_subscription_data', []);

        // remove cached campaign ids
        $campaignIds = array_filter($ids, function ($id) use ($campaignsData) {
            foreach ($campaignsData as $row) {
                foreach($row as $campaign) {
                    if ($campaign['campaign_id'] == $id) {
                        return false;
                    }
                }
            }

            return true;
        });

        if (
            ! empty($campaignsData['donationsCount'])
            || ! empty($campaignsSubscriptionData['donationsCount'])
        ) {
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

        // Fetch data from db
        $donations = CampaignsDataQuery::donations($campaignIds);

        $self->amounts = $donations->collectIntendedAmounts();
        $self->donationsCount = $donations->collectDonations();
        $self->donorsCount = $donations->collectDonors();

        // cache campaigns data
        update_option('give_campaigns_data', [
            'amounts' => array_merge($campaignsData['amounts'] ?? [], $self->amounts),
            'donationsCount' => array_merge($campaignsData['donationsCount'] ?? [], $self->donationsCount),
            'donorsCount' => array_merge($campaignsData['donorsCount'] ?? [], $self->donorsCount),
        ]);

        // Set subscriptions data
        if (defined('GIVE_RECURRING_VERSION')) {
            $subscriptions = CampaignsDataQuery::subscriptions($campaignIds);

            $self->subscriptionAmounts = $subscriptions->collectInitialAmounts();
            $self->subscriptionDonationsCount = $subscriptions->collectDonations();
            $self->subscriptionDonorsCount = $subscriptions->collectDonors();

            // cache campaigns subscriptions data
            update_option('give_campaigns_subscriptions_data', [
                'amounts' => array_merge($campaignsSubscriptionData['amounts'] ?? [], $self->subscriptionAmounts),
                'donationsCount' => array_merge($campaignsSubscriptionData['donationsCount'] ?? [], $self->subscriptionDonationsCount),
                'donorsCount' => array_merge($campaignsSubscriptionData['donorsCount'] ?? [], $self->subscriptionDonorsCount),
            ]);
        }

        return $self;
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
