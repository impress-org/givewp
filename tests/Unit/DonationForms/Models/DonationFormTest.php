<?php

namespace GiveTests\Unit\DonationForms\DataTransferObjects;

use Give\DonationForms\Models\DonationForm;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;
use GiveTests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

final class DonationFormTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    /**
     * @dataProvider mockFormTypeProvider
     *
     * @unreleased
     * @return void
     */
    public function testFindShouldReturnDonationForm(string $mockFormType)
    {
        $mockForm = $mockFormType === 'multi' ? \Give_Helper_Form::create_multilevel_form() : \Give_Helper_Form::create_simple_form();

        // create form
        $donationForm = DonationForm::find($mockForm->ID);

        $this->assertInstanceOf(DonationForm::class, $donationForm);

        // create expected donation form model using mock form
        $expectedDonationFormModel = $this->getDonationFormModelFromLegacyGiveDonateForm($mockForm);

        $this->assertSame($mockForm->get_ID(), $donationForm->id);
        $this->assertEquals($donationForm, $expectedDonationFormModel);
    }

    /**
     * @unreleased
     */
    public function mockFormTypeProvider(): array
    {
        return [
            ['multi'],
            ['simple'],
        ];
    }

}
