<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Give\NextGen\DonationForm\DataTransferObjects\DonationFormViewRouteData;
use Give\Tests\TestCase;

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
        ]);

        $this->assertSame(1, $data->formId);
    }
}
