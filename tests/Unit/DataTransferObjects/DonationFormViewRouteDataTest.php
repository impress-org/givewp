<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Give\DonationForms\DataTransferObjects\DonationFormViewRouteData;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class DonationFormViewRouteDataTest extends TestCase
{
    /**
     * @since 3.0.0
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
