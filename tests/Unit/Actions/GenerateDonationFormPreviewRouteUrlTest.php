<?php

namespace Give\Tests\Unit\Actions;

use Give\DonationForms\Actions\GenerateDonationFormPreviewRouteUrl;
use Give\Tests\TestCase;

/**
 * @since 0.1.0
 */
class GenerateDonationFormPreviewRouteUrlTest extends TestCase
{
    /**
     * @since 0.1.0
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        $viewUrl = (new GenerateDonationFormPreviewRouteUrl())(1);

        $this->assertSame(esc_url(
            add_query_arg(
                [
                    'givewp-route' => 'donation-form-view-preview',
                    'form-id' => 1
                ],
                site_url()
            )
        ), $viewUrl);
    }
}
