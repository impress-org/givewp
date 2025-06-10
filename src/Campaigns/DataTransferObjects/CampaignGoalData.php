<?php

namespace Give\Campaigns\DataTransferObjects;

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\CampaignSubscriptionQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * @since 4.0.0
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
    public $goal;

    /**
     * @var int|string
     */
    public $goalFormatted;

    /**
     * @var int|string
     */
    public $actualFormatted;

    /**
     * @since 4.0.0
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
     * @since 4.2.0 return union type int|float
     * @since 4.0.0
     *
     * @return int|float
     */
    private function getActual()
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
     * @since 4.0.0
     */
    private function getPercentage(): float
    {
        $percentage = $this->campaign->goal
            ? $this->actual / $this->campaign->goal
            : 0;
        return round($percentage * 100, 2);
    }

    /**
     * @since 4.0.0
     */
    private function getActualFormatted(): string
    {
        if ($this->campaign->goalType->isAmount()) {
            return give_currency_filter(give_format_amount($this->actual));
        }

        return $this->actual;
    }

    /**
     * @since 4.0.0
     */
    private function getGoalFormatted(): string
    {
        if ($this->campaign->goalType->isAmount()) {
            return give_currency_filter(give_format_amount($this->goal));
        }

        return $this->goal;
    }

    /**
     * @since 4.0.0
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
