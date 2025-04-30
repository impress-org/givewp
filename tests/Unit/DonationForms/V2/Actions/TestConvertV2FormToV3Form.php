<?php

namespace Give\Tests\Unit\DonationForms\V2\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\Actions\ConvertV2FormToV3Form;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * @unreleased
 * @coversDefaultClass \Give\DonationForms\V2\Actions\ConvertV2FormToV3Form
 */
class TestConvertV2FormToV3Form extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testConvertV2FormToV3Form()
    {
        $v2Form = $this->createSimpleDonationForm();

        $v3Form = (new ConvertV2FormToV3Form($v2Form))();

        $this->assertInstanceOf(DonationForm::class, $v3Form);
    }
}
