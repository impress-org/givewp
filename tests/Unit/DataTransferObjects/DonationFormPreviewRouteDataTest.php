<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Give\DonationForms\DataTransferObjects\DonationFormPreviewRouteData;
use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class DonationFormPreviewRouteDataTest extends TestCase
{
    /**
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
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
