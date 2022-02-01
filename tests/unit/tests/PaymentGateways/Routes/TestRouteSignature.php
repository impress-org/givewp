<?php

use Give\Framework\PaymentGateways\Routes\RouteSignature;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass RouteSignature
 */
class TestRouteSignature extends TestCase
{
    public function testRouteSignatureReturnsValidString()
    {
        $args = ['give-donation-id' => 1];
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';

        $action = new RouteSignature($gatewayId, $gatewayMethod, $args);

        $secureArgs = md5(implode('|', $args));

        $signature = "$gatewayId@$gatewayMethod:$secureArgs";

        $this->assertEquals($action->toString(), $signature);
    }

    public function testRouteSignatureReturnsValidNonce()
    {
        $args = ['give-donation-id' => 1];
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';

        $action = new RouteSignature($gatewayId, $gatewayMethod, $args);

        $secureArgs = md5(implode('|', $args));

        $signature = "$gatewayId@$gatewayMethod:$secureArgs";

        $this->assertEquals(1, wp_verify_nonce($action->toNonce(), $signature));
    }
}
