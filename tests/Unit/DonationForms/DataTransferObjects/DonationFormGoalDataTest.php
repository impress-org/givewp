<?php

namespace Give\Tests\Unit\DonationForms\DataTransferObjects;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\DataTransferObjects\DonationFormGoalData;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\DonationForms\ValueObjects\GoalSource;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Framework\Database\DB;
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
        /**
         * @var DonationForm $donationForm
         */
        $donationForm = DonationForm::factory()->create([
            'settings' => FormSettings::fromArray([
                'goalSource' => GoalSource::FORM()
            ]),
        ]);

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
            'percentage' => $progressPercentage,
            'isAchieved' => $isEnabled && $donationForm->settings->enableAutoClose && $progressPercentage >= 100
        ]);
    }

    /**
     * Mirrors a Peer-to-Peer campaign that links its form only through give_campaigns.form_id, which
     * Campaign::findByFormId() does not resolve, so the form carries the campaign goal source with no
     * campaign behind it.
     *
     * @since TBD
     */
    public function testFallsBackToTheFormGoalWhenTheCampaignGoalHasNoCampaign()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create([
            'settings' => FormSettings::fromArray([
                'goalSource' => GoalSource::CAMPAIGN(),
                'goalType' => GoalType::DONATIONS(),
                'goalAmount' => 30,
                'enableDonationGoal' => true,
            ]),
        ]);

        $campaign = Campaign::factory()->create();

        DB::table('give_campaign_forms')->where('form_id', $donationForm->id)->delete();
        DB::table('give_campaigns')
            ->where('id', $campaign->id)
            ->update(['form_id' => $donationForm->id]);

        $this->assertNull(Campaign::findByFormId($donationForm->id));

        $goalData = (new DonationFormGoalData($donationForm->id, $donationForm->settings))->toArray();

        $this->assertSame(GoalType::DONATIONS, $goalData['type']);
        $this->assertSame(30, $goalData['targetAmount']);
    }
}
