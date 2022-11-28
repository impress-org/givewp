<?php

namespace TestsNextGen\Unit\DonationForm\VieModels;

use Give\NextGen\DonationForm\DataTransferObjects\DonationFormGoalData;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\ValueObjects\GoalType;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

class DonationFormGoalDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testToArrayShouldReturnExpectedArrayOfData()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $donationFormGoalData = new DonationFormGoalData($donationForm->id, $donationForm->settings);
        $currentAmount = $donationFormGoalData->getCurrentAmount();
        $isEnabled = $donationForm->settings->enableDonationGoal ?? false;
        $goalType = $donationForm->settings->goalType ?? GoalType::AMOUNT();
        $targetAmount = $donationForm->settings->goalAmount ?? 0;

        $this->assertEquals($donationFormGoalData->toArray(), [
            'type' => $goalType->getValue(),
            'typeIsCount' => !$goalType->isAmount(),
            'typeIsMoney' => $goalType->isAmount(),
            'enabled' => $isEnabled,
            'show' => $isEnabled,
            'currentAmount' => $currentAmount,
            'targetAmount' => $targetAmount,
            'label' => $goalType->isDonors() ? __('donors', 'give') : __('donations', 'give'),
            'progressPercentage' => !$currentAmount ? 0 : ($currentAmount / $targetAmount) * 100
        ]);
    }
}
