<?php

namespace Give\DonationForms\Properties;

use Give\DonationForms\ValueObjects\GoalType;

/**
 * @since 4.5.0 Add default values to goal settings
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
        $settings->goalSource = $data['goalSource'] ?? '';
        $settings->enableDonationGoal = $data['enableDonationGoal'] ?? false;
        $settings->goalType = new GoalType($data['goalType'] ?? GoalType::AMOUNT);
        $settings->goalAmount = $data['goalAmount'] ?? 0;

        return $settings;
    }
}
