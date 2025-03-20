<?php

namespace Give\Campaigns\Models;

use Give\Campaigns\ValueObjects\CampaignGoalType;

/**
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
    private $subscriptionAmounts;
    /**
     * @var array
     */
    private $donationsCount;
    /**
     * @var array
     */
    private $subscriptionDonationsCount;
    /**
     * @var array
     */
    private $donorsCount;
    /**
     * @var array
     */
    private $subscriptionDonorsCount;

    public static function fromArray(array $data): CampaignsData
    {
        $self = new static();
        $self->amounts = $data['amounts'];
        $self->subscriptionAmounts = $data['subscription_amounts'] ?? [];
        $self->donationsCount = $data['donations'];
        $self->subscriptionDonationsCount = $data['subscriptions_donations'] ?? [];
        $self->donorsCount = $data['donors'];
        $self->subscriptionDonorsCount = $data['subscription_donors'] ?? [];

        return $self;
    }

    /**
     * @unreleased
     *
     * @param Campaign $campaign
     *
     * @return string
     */
    public function getRevenue(Campaign $campaign): string
    {
        $data = $campaign->goalType->isSubscriptions()
            ? $this->subscriptionAmounts
            : $this->amounts;

        foreach ($data as $row) {
            if (isset($row['campaign_id']) && $row['campaign_id'] == $campaign->id) {
                return $row['sum'];
            }
        }

        return give_currency_filter(0);
    }

    /**
     * @unreleased
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
     * @param Campaign $campaign
     *
     * @return array
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
