<?php

namespace Give\DonationForms\Properties;

use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\DonationForms\ValueObjects\GoalSource;
use Give\DonationForms\ValueObjects\GoalType;

/**
 * @unreleased
 */
class GoalSettings
{
    public string $goalSource;
    public float $goalAmount;
    public $goalType;
    public bool $enableDonationGoal;

    public static function fromArray(array $data): GoalSettings
    {
        $settings = new self();
        $settings->goalSource = $data['goalSource'];
        $settings->enableDonationGoal = $data['enableDonationGoal'];
        $settings->goalType = $settings->goalSource === GoalSource::CAMPAIGN
            ? new CampaignGoalType($data['goalType'])
            : new GoalType($data['goalType']);
        $settings->goalAmount = $data['goalAmount'];

        return $settings;
    }
}
