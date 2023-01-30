<?php

namespace Give\Tests\Unit\Actions;

use Give\NextGen\DonationForm\Actions\GenerateDonationFormViewRouteUrl;
use Give\Tests\TestCase;

/**
 * @since 0.1.0
 */
class GenerateDonationFormViewRouteUrlTest extends TestCase
{
    /**
     * @since 0.1.0
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        $viewUrl = (new GenerateDonationFormViewRouteUrl())(1);

        $this->assertSame(esc_url_raw(
            add_query_arg(
                [
                    'givewp-route' => 'donation-form-view',
                    'form-id' => 1
                ],
                home_url()
            )
        ), $viewUrl);
    }
}
