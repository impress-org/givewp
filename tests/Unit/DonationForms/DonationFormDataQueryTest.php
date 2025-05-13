<?php

namespace Give\Tests\Unit\DonationForms;

use Give\DonationForms\DonationFormDataQuery;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class DonationFormDataQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
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

        $formsDataQuery = DonationFormDataQuery::donations([$form]);

        $this->assertEquals([
            [
                'sum' => 30.32,
                'form_id' => $form->id,
            ]
        ], $formsDataQuery->collectIntendedAmounts());
    }
}
