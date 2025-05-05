<?php

namespace Give\DonationForms\Adapter;

use Give\DonationForms\ValueObjects\GoalType;

/**
 * @unreleased
 */
class GoalSettings
{
    public float $goalAmount;
    public GoalType $goalType;
    public bool $enableDonationGoal;

    public static function fromArray(array $data): GoalSettings
    {
        $settings = new static();
        $settings->enableDonationGoal = true;
        $settings->goalType = new GoalType($data['goalType']);
        $settings->goalAmount = $data['goalAmount'];

        return $settings;
    }
}
