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
     * @param int[] $ids
     *
     * @return CampaignsDataRepository
     */
    public static function campaigns(array $ids): CampaignsDataRepository
    {
        $self = new self();

        $donations = CampaignsDataQuery::donations($ids);

        $self->amounts = $donations->collectIntendedAmounts();
        $self->donationsCount = $donations->collectDonations();
        $self->donorsCount = $donations->collectDonors();

        // Set subscriptions data
        if (defined('GIVE_RECURRING_VERSION')) {
            $subscriptions = CampaignsDataQuery::subscriptions($ids);

            $self->subscriptionAmounts = $subscriptions->collectInitialAmounts();
            $self->subscriptionDonationsCount = $subscriptions->collectDonations();
            $self->subscriptionDonorsCount = $subscriptions->collectDonors();
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
