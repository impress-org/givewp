<?php

namespace unit\tests\Framework\Routes;

use Give\Framework\PaymentGateways\Routes\RouteSignature;
use PHPUnit\Framework\TestCase;

use function wp_verify_nonce;

/**
 * @since 2.19.0
 *
 * @coversDefaultClass RouteSignature
 */
class TestRouteSignature extends TestCase
{
    /**
     * @@since 2.19.4 - replace RouteSignature args with unique donationId
     * @since 2.19.0
     *
     * @return void
     */
    public function testRouteSignatureReturnsValidString()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;

        $action = new RouteSignature($gatewayId, $gatewayMethod, 1);

        $signature = "$gatewayId@$gatewayMethod:$donationId";

        $this->assertEquals($action->toString(), $signature);
    }

    /**
     * @@since 2.19.4 - replace RouteSignature args with unique donationId
     * @since 2.19.0
     *
     * @return void
     */
    public function testRouteSignatureReturnsValidNonce()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;

        $action = new RouteSignature($gatewayId, $gatewayMethod, $donationId);

        $signature = "$gatewayId@$gatewayMethod:$donationId";

        $this->assertEquals(1, wp_verify_nonce($action->toNonce(), $signature));
    }
}
