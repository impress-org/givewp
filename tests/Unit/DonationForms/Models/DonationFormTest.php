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
        $mockForm = $mockFormType === 'multi' ? $this->createMultiLevelDonationForm() : $this->createSimpleDonationForm(
        );

        $donationForm = DonationForm::find($mockForm->id);

        $this->assertInstanceOf(DonationForm::class, $donationForm);
        $this->assertSame($mockForm->id, $donationForm->id);
        $this->assertEquals($donationForm, $mockForm);
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
