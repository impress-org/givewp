<?php

namespace Give\Tests\Unit\DonationForms\VieModels;

use Give\DonationForms\DataTransferObjects\DonationFormGoalData;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class DonationFormGoalDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     */
    public function testToArrayShouldReturnExpectedArrayOfData()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $donationFormGoalData = new DonationFormGoalData($donationForm->id, $donationForm->settings);
        $currentAmount = $donationFormGoalData->getCurrentAmount();
        $isEnabled = $donationForm->settings->enableDonationGoal ?? false;
        $goalType = $donationForm->settings->goalType ?? GoalType::AMOUNT();
        $goalTypeIsAmount = $donationForm->settings->goalType->isOneOf(GoalType::AMOUNT(), GoalType::AMOUNT_FROM_SUBSCRIPTIONS());
        $targetAmount = $donationForm->settings->goalAmount ?? 0;
        $progressPercentage = !$currentAmount ? 0 : ($currentAmount / $targetAmount) * 100;

        $this->assertEquals($donationFormGoalData->toArray(), [
            'type' => $goalType->getValue(),
            'typeIsCount' => !$goalTypeIsAmount,
            'typeIsMoney' => $goalTypeIsAmount,
            'enabled' => $isEnabled,
            'show' => $isEnabled,
            'currentAmount' => $currentAmount,
            'targetAmount' => $targetAmount,
            'label' => $donationFormGoalData->getLabel(),
            'isAchieved' => $isEnabled && $donationForm->settings->enableAutoClose && $progressPercentage >= 100
        ]);
    }
}
