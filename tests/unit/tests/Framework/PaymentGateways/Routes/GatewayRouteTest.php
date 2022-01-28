<?php

use Give\Framework\PaymentGateways\Actions\GenerateGatewayRouteUrl;
use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use Give\Helpers\Call;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 */
class GatewayRouteTest extends TestCase
{
    public function testThrowExceptionOnInvalidGatewayId()
    {
        $gatewayRoute = new GatewayRoute();

        $gatewayRouteData = GatewayRouteData::fromRequest(
            [
                'give-gateway-id' => 'mock-payapl-offsite',
                'give-gateway-method' => 'returnSuccessFromOffsiteRedirect',
                'give-donation-id' => 123,
                '_wpnonce' => ''
            ]
        );

        $this->expectExceptionMessage('This route is not valid.');
        $gatewayRoute->process($gatewayRouteData);
    }
}
