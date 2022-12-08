<?php

namespace Give\Tests\Unit\Actions;

use Give\NextGen\DonationForm\Actions\GenerateDonateRouteUrl;
use Give\NextGen\DonationForm\Routes\DonateRouteSignature;
use Give\Tests\TestCase;

class GenerateDonateRouteUrlTest extends TestCase {
    /**
     * @unreleased
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        $url = (new GenerateDonateRouteUrl())();

        $signature = new DonateRouteSignature('give-donate');

        $queryArgs = [
            'give-listener' => 'give-donate',
            'give-route-signature' => $signature->toHash(),
            'give-route-signature-id' => 'give-donate',
            'give-route-signature-expiration' => $signature->expiration,
        ];

        $mockUrl = esc_url_raw(add_query_arg($queryArgs, home_url()));

        $this->assertSame($mockUrl, $url);
    }
}
