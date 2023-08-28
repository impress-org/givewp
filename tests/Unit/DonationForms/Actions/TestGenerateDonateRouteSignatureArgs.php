<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\GenerateDonateRouteSignatureArgs;
use Give\DonationForms\Routes\DonateRouteSignature;
use Give\Tests\TestCase;

final class TestGenerateDonateRouteSignatureArgs extends TestCase
{
    /**
     * @since 3.0.0
     */
    public function testShouldReturnArrayOfQueryArgs()
    {
        $signature = new DonateRouteSignature('givewp-donate');

        $queryArgs = (new GenerateDonateRouteSignatureArgs())($signature, 'givewp-donate');

        $this->assertEquals([
            'givewp-route-signature' => $signature->toHash(),
            'givewp-route-signature-id' => 'givewp-donate',
            'givewp-route-signature-expiration' => $signature->expiration,
        ], $queryArgs);
    }
}
