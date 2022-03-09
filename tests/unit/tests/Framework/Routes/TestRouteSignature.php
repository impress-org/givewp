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
     * @unreleased - remove args from RouteSignature
     * @since 2.19.0
     *
     * @return void
     */
    public function testRouteSignatureReturnsValidString()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';

        $action = new RouteSignature($gatewayId, $gatewayMethod);

        $signature = "$gatewayId@$gatewayMethod";

        $this->assertEquals($action->toString(), $signature);
    }

    /**
     * @unreleased - remove args from RouteSignature
     * @since 2.19.0
     *
     * @return void
     */
    public function testRouteSignatureReturnsValidNonce()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';

        $action = new RouteSignature($gatewayId, $gatewayMethod);

        $signature = "$gatewayId@$gatewayMethod";

        $this->assertEquals(1, wp_verify_nonce($action->toNonce(), $signature));
    }
}
