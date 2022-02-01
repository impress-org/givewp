<?php

namespace unit\tests\Framework\Routes;

use Give\Framework\PaymentGateways\Routes\RouteSignature;
use PHPUnit\Framework\TestCase;

use function wp_verify_nonce;

/**
 * @unreleased
 *
 * @coversDefaultClass RouteSignature
 */
class TestRouteSignature extends TestCase
{
    /**
     * @unreleased
     *
     * @return void
     */
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

    /**
     * @unreleased
     *
     * @return void
     */
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
