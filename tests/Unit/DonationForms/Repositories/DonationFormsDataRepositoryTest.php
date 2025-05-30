<?php

namespace Give\Tests\Unit\DonationForms\Repositories;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\Models\DonationForm as LegacyDonationForm;
use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.3.0
 */
final class DonationFormsDataRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.3.0
     */
    public function testCountFormsDonations()
    {
        $form = DonationForm::factory()->create();
        $legacyDonationForm = LegacyDonationForm::find($form->id);

        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);
        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $formsData = DonationFormDataRepository::forms([$legacyDonationForm]);

        $this->assertEquals(2, $formsData->getDonationsCount($legacyDonationForm));
    }

    /**
     * @since 4.3.0
     */
    public function testGetRevenueReturnsSumOfDonationsWithoutRecoveredFees()
    {
        $form = DonationForm::factory()->create();
        $legacyDonationForm = LegacyDonationForm::find($form->id);

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

        $formsData = DonationFormDataRepository::forms([$legacyDonationForm]);

        $this->assertEquals(20.32, $formsData->getRevenue($legacyDonationForm));
    }

    /**
     * @since 4.3.0
     */
    public function testCountFormDonors()
    {
        $form = DonationForm::factory()->create();
        $legacyDonationForm = LegacyDonationForm::find($form->id);

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

        $formsData = DonationFormDataRepository::forms([$legacyDonationForm]);

        $this->assertEquals(2, $formsData->getDonorsCount($legacyDonationForm));
    }
}
