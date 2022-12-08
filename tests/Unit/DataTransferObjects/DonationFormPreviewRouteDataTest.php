<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Give\NextGen\DonationForm\DataTransferObjects\DonationFormPreviewRouteData;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class DonationFormPreviewRouteDataTest extends TestCase
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnFormId()
    {
        $data = DonationFormPreviewRouteData::fromRequest([
            'form-id' => '1',
            'form-template-id' => 'classic',
        ]);

        $this->assertSame(1, $data->formId);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnFormSettings()
    {
        $data = DonationFormPreviewRouteData::fromRequest([
            'form-id' => '1',
            'form-settings' => json_encode([
                'designId' => 'classic',
            ]),
        ]);

        $this->assertSame('classic', $data->formSettings->designId);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnFormBlocks()
    {
        $data = DonationFormPreviewRouteData::fromRequest([
            'form-id' => '1',
            'form-template-id' => 'classic',
            'form-blocks' => DonationForm::factory()->definition()['blocks']->toJson(),
        ]);

        $this->assertEquals(DonationForm::factory()->definition()['blocks'], $data->formBlocks);
    }

}
