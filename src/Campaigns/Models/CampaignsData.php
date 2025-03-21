<?php

namespace Give\Campaigns\Models;

use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\ValueObjects\CampaignGoalType;

/**
 * Used to optimize the campaigns list table performance and to avoid n+1 problems.
 * Instead of doing expensive queries in multiple columns in each row, this class loads everything upfront for a range of campaigns.
 *
 * @unreleased
 */
class CampaignsData
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
     * @return CampaignsData
     */
    public static function campaigns(array $ids): CampaignsData
    {
        $self = new self();

        $core = CampaignsDataQuery::donations($ids);

        $self->amounts = $core->collectIntendedAmounts();
        $self->donationsCount = $core->collectDonations();
        $self->donorsCount = $core->collectDonors();

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
     * @unreleased
     *
     * Get revenue for campaign
     *
     * @param Campaign $campaign
     *
     * @return int
     */
    public function getRevenue(Campaign $campaign): int
    {
        $data = $campaign->goalType->isSubscriptions()
            ? $this->subscriptionAmounts
            : $this->amounts;

        foreach ($data as $row) {
            if (isset($row['campaign_id']) && $row['campaign_id'] == $campaign->id) {
                return (int)$row['sum'];
            }
        }

        return 0;
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     *
     * Get goal data for campaign
     *
     * @param Campaign $campaign
     *
     * @return array{actual: int|float, actualFormatted: string, goalFormatted:string, percentage:float}
     */
    public function getGoalData(Campaign $campaign): array
    {
        $actual = $this->getActualGoal($campaign);
        $percentage = $campaign->goal
            ? $actual / $campaign->goal
            : 0;

        return [
            'actual' => $actual,
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
     * @unreleased
     *
     * @param Campaign $campaign
     *
     * @return int|string
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
