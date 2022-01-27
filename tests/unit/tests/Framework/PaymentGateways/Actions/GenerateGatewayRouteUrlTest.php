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
            'example.org?give-listener=give-gateway&give-gateway-id=test-gateway&give-gateway-method=returnCancelFromOffsiteRedirect&give-donation-id=123',
            $url
        );
    }

    public function testGeneratedUrlWithNonce()
    {
        $url = Call::invoke(
            GenerateGatewayRouteUrl::class,
            'test-gateway',
            'returnCancelFromOffsiteRedirect',
            '123',
            null,
            [ 'withNonce' => true ]
        );

        $this->assertContains(
            'example.org?give-listener=give-gateway&give-gateway-id=test-gateway&give-gateway-method=returnCancelFromOffsiteRedirect&give-donation-id=123&_wpnonce=',
            $url
        );
    }

    public function testGeneratedUrlHasAdditionalQueryArgs()
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
            '&paramOne=1&paramTwo=2',
            $url
        );
    }
}
