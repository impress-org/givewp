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
     * @unreleased
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->actual = $this->getActual();
        $this->percentage = $this->getPercentage();
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
    private function getPercentage(): int
    {
        return round($this->actual / $this->campaign->goal * 100);
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return [
            'actual' => $this->actual,
            'percentage' => $this->percentage,
        ];
    }
}
