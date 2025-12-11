<?php

namespace Give\Tests\Unit\DonationForms;

use Give\DonationForms\DonationFormDataQuery;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\Models\DonationForm as LegacyDonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.3.0
 */
final class DonationFormDataQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.3.0
     */
    public function testCollectInitialAmounts()
    {
        $form = DonationForm::factory()->create();

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


        Donation::factory()->create([
            'formId' => $form->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money(1000, 'USD'),
        ]);

        $formsDataQuery = DonationFormDataQuery::donations([$form->id]);

        $this->assertEquals([
            [
                'sum' => 30.32,
                'form_id' => $form->id,
            ]
        ], $formsDataQuery->collectIntendedAmounts());
    }
}
