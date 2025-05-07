<?php

namespace Give\Tests\Unit\DonationForms\Repositories;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignGoalType;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class DonationFormsDataRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testCountFormsDonations()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        $form = DonationForm::find($campaign->defaultFormId);

        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $formsData = DonationFormDataRepository::forms([$form->id]);

        $this->assertEquals(2, $formsData->getDonationsCount($form));
    }

    /**
     * @unreleased
     */
    public function testGetRevenueReturnsSumOfDonationsWithoutRecoveredFees()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        $form = DonationForm::find($campaign->defaultFormId);

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1051, 'USD'),
            'feeAmountRecovered' => new Money(35, 'USD'),
        ]);

        $formsData = DonationFormDataRepository::forms([$form->id]);

        $this->assertEquals(20.32, $formsData->getRevenue($form));
    }

    /**
     * @unreleased
     */
    public function testCountFormDonors()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'goalType' => CampaignGoalType::AMOUNT(),
        ]);

        $form = DonationForm::find($campaign->defaultFormId);

        $donor = Donor::factory()->create();
        $donor2 = Donor::factory()->create();

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'donorId' => $donor->id,
        ]);

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
            'donorId' => $donor2->id,
        ]);

        $formsData = DonationFormDataRepository::forms([$form->id]);

        $this->assertEquals(2, $formsData->getDonorsCount($form));
    }
}
