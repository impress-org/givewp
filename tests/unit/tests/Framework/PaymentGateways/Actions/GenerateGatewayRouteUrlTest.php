<?php

use Give\Framework\PaymentGateways\Actions\GenerateGatewayRouteUrl;
use Give\Helpers\Call;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 */
class GenerateGatewayRouteUrlTest extends TestCase
{
    public function testGeneratedUrl()
    {
        $url = Call::invoke(
            GenerateGatewayRouteUrl::class,
            'test-gateway',
            'returnCancelFromOffsiteRedirect',
            '123'
        );

        $this->assertContains(
            'example.org?give-listener=give-gateway&amp;give-gateway-id=test-gateway&amp;give-gateway-method=returnCancelFromOffsiteRedirect&amp;give-donation-id=123&amp;_wpnonce=',
            $url
        );
    }

    public function testGeneratedUrlHasAdditionaQueryArgs()
    {
        $url = Call::invoke(
            GenerateGatewayRouteUrl::class,
            'test-gateway',
            'returnCancelFromOffsiteRedirect',
            '123',
            [
                'paramOne' => 1,
                'paramTwo' => 2
            ]
        );

        $this->assertContains(
            '&amp;paramOne=1&amp;paramTwo=2',
            $url
        );
    }
}
