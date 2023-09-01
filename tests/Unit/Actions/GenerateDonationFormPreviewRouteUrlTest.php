<?php

namespace Give\Tests\Unit\Actions;

use Give\DonationForms\Actions\GenerateDonationFormPreviewRouteUrl;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class GenerateDonationFormPreviewRouteUrlTest extends TestCase
{
    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        $viewUrl = (new GenerateDonationFormPreviewRouteUrl())(1);

        $this->assertSame(
            add_query_arg(
                [
                    'givewp-route' => 'donation-form-view-preview',
                    'form-id' => 1,
                ],
                site_url()
            ),
            $viewUrl
        );
    }
}
