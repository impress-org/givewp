<?php

namespace Give\DonationForms\Properties;

use Give\DonationForms\ValueObjects\GoalType;

/**
 * @since 4.3.0
 */
class GoalSettings
{
    public string $goalSource;
    public float $goalAmount;
    public GoalType $goalType;
    public bool $enableDonationGoal;

    public static function fromArray(array $data): GoalSettings
    {
        $settings = new self();
        $settings->goalSource = $data['goalSource'];
        $settings->enableDonationGoal = $data['enableDonationGoal'];
        $settings->goalType = new GoalType($data['goalType']);
        $settings->goalAmount = $data['goalAmount'];

        return $settings;
    }
}
