<?php

namespace Give\Campaigns\DataTransferObjects;

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\CampaignSubscriptionQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * @unreleased
 */
class CampaignGoalData implements Arrayable
{
    /**
     * @var Campaign
     */
    private $campaign;

    /**
     * @var int
     */
    public $actual;

    /**
     * @var int
     */
    public $percentage;

    /**
     * @var int
     */
    private $goal;

    /**
     * @var int|string
     */
    public $goalFormatted;

    /**
     * @var int|string
     */
    public $actualFormatted;

    /**
     * @unreleased
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->actual = $this->getActual();
        $this->actualFormatted = $this->getActualFormatted();
        $this->percentage = $this->getPercentage();
        $this->goal = $campaign->goal;
        $this->goalFormatted = $this->getGoalFormatted();
    }

    /**
     * @unreleased
     */
    private function getActual(): int
    {
        $query = $this->campaign->goalType->isOneOf(
            CampaignGoalType::SUBSCRIPTIONS(),
            CampaignGoalType::AMOUNT_FROM_SUBSCRIPTIONS(),
            CampaignGoalType::DONORS_FROM_SUBSCRIPTIONS()
        )
            ? new CampaignSubscriptionQuery($this->campaign)
            : new CampaignDonationQuery($this->campaign);

        switch ($this->campaign->goalType->getValue()) {
            case CampaignGoalType::DONATIONS():
            case CampaignGoalType::SUBSCRIPTIONS():
                return $query->countDonations();

            case CampaignGoalType::DONORS():
            case CampaignGoalType::DONORS_FROM_SUBSCRIPTIONS():
                return $query->countDonors();

            case CampaignGoalType::AMOUNT_FROM_SUBSCRIPTIONS():
                return $query->sumInitialAmount();

            case CampaignGoalType::AMOUNT():
            default:
                return $query->sumIntendedAmount();
        }
    }

    /**
     * @unreleased
     */
    private function getPercentage(): float
    {
        $percentage = $this->campaign->goal
            ? $this->actual / $this->campaign->goal
            : 0;
        return round($percentage * 100, 2);
    }

    /**
     * @unreleased
     */
    private function getActualFormatted(): string
    {
        if ($this->campaign->goalType == CampaignGoalType::AMOUNT) {
            return give_currency_filter(give_format_amount($this->actual));
        }

        return $this->actual;
    }

    /**
     * @unreleased
     */
    private function getGoalFormatted(): string
    {
        if ($this->campaign->goalType == CampaignGoalType::AMOUNT) {
            return give_currency_filter(give_format_amount($this->goal));
        }

        return $this->goal;
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return [
            'actual' => $this->actual,
            'actualFormatted' => $this->actualFormatted,
            'percentage' => $this->percentage,
            'goal' => $this->goal,
            'goalFormatted' => $this->goalFormatted,
        ];
    }
}
