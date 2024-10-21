<?php

namespace Give\Campaigns\DataTransferObjects;

use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\ValueObjects\GoalType;
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
        $query = new CampaignDonationQuery($this->campaign);

        switch ($this->campaign->goalType->getValue()) {
            case GoalType::DONATIONS():
                return $query->countDonations();

            case GoalType::DONORS():
                return $query->countDonors();

            case GoalType::AMOUNT():
            default:
                return $query->sumIntendedAmount();
        }
    }

    /**
     * @unreleased
     */
    private function getPercentage(): float
    {
        return round($this->actual / $this->campaign->goal * 100, 2);
    }

    /**
     * @unreleased
     */
    private function getActualFormatted(): string
    {
        if ($this->campaign->goalType == GoalType::AMOUNT) {
            return give_currency_filter(give_format_amount($this->actual));
        }

        return $this->actual;
    }

    /**
     * @unreleased
     */
    private function getGoalFormatted(): string
    {
        if ($this->campaign->goalType == GoalType::AMOUNT) {
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
