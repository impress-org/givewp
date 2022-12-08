<?php

namespace Give\Tests\Unit\Actions;

use Give\NextGen\DonationForm\Actions\GenerateDonationFormViewRouteUrl;
use Give\Tests\TestCase;

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
        $viewUrl = (new GenerateDonationFormViewRouteUrl())(1);

        $this->assertSame(esc_url_raw(
            add_query_arg(
                [
                    'givewp-view' => 'donation-form',
                    'form-id' => 1
                ],
                home_url()
            )
        ), $viewUrl);
    }
}
