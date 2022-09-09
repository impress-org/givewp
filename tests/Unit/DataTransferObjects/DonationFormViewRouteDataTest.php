<?php

namespace TestsNextGen\Unit\DataTransferObjects;

use Give\NextGen\DonationForm\DataTransferObjects\DonationFormViewRouteData;
use GiveTests\TestCase;

/**
 * @unreleased
 */
class DonationFormViewRouteDataTest extends TestCase
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnFormId()
    {
        $data = DonationFormViewRouteData::fromRequest([
            'form-id' => '1',
            'form-template-id' => 'classic',
        ]);

        $this->assertSame('1', $data->formId);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnFormTemplateId()
    {
        $data = DonationFormViewRouteData::fromRequest([
            'form-id' => '1',
            'form-template-id' => 'classic',
        ]);

        $this->assertSame('classic', $data->formTemplateId);
    }

}
