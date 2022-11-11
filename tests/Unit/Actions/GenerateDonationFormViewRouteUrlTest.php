<?php

namespace TestsNextGen\Unit\Actions;

use Give\NextGen\DonationForm\Actions\GenerateDonationFormViewRouteUrl;
use GiveTests\TestCase;

/**
 * @unreleased
 */
class GenerateDonationFormViewRouteUrlTest extends TestCase
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        $viewUrl = (new GenerateDonationFormViewRouteUrl())(1, ['templateId' => 'classic']);

        $this->assertSame(esc_url_raw(
            add_query_arg(
                [
                    'givewp-view' => 'donation-form',
                    'form-id' => 1,
                    'form-settings' => [
                        'templateId' => 'classic'
                    ]
                ],
                home_url()
            )
        ), $viewUrl);
    }
}
