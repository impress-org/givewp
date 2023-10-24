<?php

namespace Give\Tests\Unit\Actions;

use Give\DonationForms\Actions\GenerateDonateRouteUrl;
use Give\DonationForms\Routes\DonateRouteSignature;
use Give\Tests\TestCase;

class GenerateDonateRouteUrlTest extends TestCase
{
    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        $url = (new GenerateDonateRouteUrl())();

        $signature = new DonateRouteSignature('givewp-donate');

        $queryArgs = [
            'givewp-route' => 'donate',
            'givewp-route-signature' => $signature->toHash(),
            'givewp-route-signature-id' => 'givewp-donate',
            'givewp-route-signature-expiration' => $signature->expiration,
        ];

        $mockUrl = esc_url_raw(add_query_arg($queryArgs, home_url()));

        $this->assertSame($mockUrl, $url);
    }
}
