<?php

namespace Give\Tests\Unit\Actions;

use Give\NextGen\DonationForm\Actions\GenerateDonationFormPreviewRouteUrl;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class GenerateDonationFormPreviewRouteUrlTest extends TestCase
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        $viewUrl = (new GenerateDonationFormPreviewRouteUrl())(1);

        $this->assertSame(esc_url(
            add_query_arg(
                [
                    'givewp-view' => 'donation-form-preview',
                    'form-id' => 1
                ],
                site_url()
            )
        ), $viewUrl);
    }
}
