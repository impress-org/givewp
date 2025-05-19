<?php

namespace Give\Tests\Unit\Actions;

use Give\DonationForms\Actions\GenerateDonationFormViewRouteUrl;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class GenerateDonationFormViewRouteUrlTest extends TestCase
{
    /**
     * @since 4.3.0 Use trailingslashit() method to prevent errors on websites installed in subdirectories
     * @since 3.0.0
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
                trailingslashit(home_url())
            )
        ), $viewUrl);
    }
}
