<?php

use Give\Framework\PaymentGateways\Actions\GenerateGatewayRouteUrl;
use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 */
class GatewayRouteTest extends TestCase
{
    /**
     * @var GatewayRoute
     */
    private $gatewayRoute;

    /**
     * @var MockPaypalOffsite $mockPayPalOffsite
     */
    private $mockPayPalOffsite;

    protected function setUp()
    {
        $this->gatewayRoute = new GatewayRoute();
        $this->mockPayPalOffsite = $this->getMockForAbstractClass(
            MockPaypalOffsite::class,
            [],
            '',
            true,
            true,
            true,
            ['returnSuccessFromOffsiteRedirect', 'handleGatewayRouteMethod']
        );
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();

        // De register gateway.
        give(PaymentGatewayRegister::class)->unregisterGateway($this->mockPayPalOffsite->getId());

        // Remove mock class from container.
        give()->offsetUnset(get_class($this->mockPayPalOffsite));
    }

    public function testThrowExceptionOnInvalidGatewayId()
    {
        $gatewayRouteData = GatewayRouteData::fromRequest(
            [
                'give-gateway-id' => 'mock-payapl-offsite',
                'give-gateway-method' => 'returnSuccessFromOffsiteRedirect',
                'give-donation-id' => 123,
                '_wpnonce' => ''
            ]
        );

        $this->expectExceptionMessage('This route is not valid.');
        $this->gatewayRoute->process($gatewayRouteData);
    }

    public function testThrowExceptionOnInvalidNonce()
    {
        give(PaymentGatewayRegister::class)->registerGateway(get_class($this->mockPayPalOffsite));

        $gatewayRouteData = GatewayRouteData::fromRequest(
            [
                'give-gateway-id' => $this->mockPayPalOffsite->getId(),
                'give-gateway-method' => 'returnSuccessFromOffsiteRedirect',
                'give-donation-id' => 123,
                '_wpnonce' => 'demo'
            ]
        );

        $this->expectExceptionMessage('This route does not have valid nonce.');
        $this->gatewayRoute->process($gatewayRouteData);
    }

    public function testThrowExceptionOnInvalidGatewayCallback()
    {
        give(PaymentGatewayRegister::class)->registerGateway(get_class($this->mockPayPalOffsite));

        $gatewayRouteData = [
            'give-gateway-id' => $this->mockPayPalOffsite->getId(),
            'give-gateway-method' => 'thisCallbackDoesNotExistInMockClass',
            'give-donation-id' => 123,
        ];

        $gatewayRouteData['_wpnonce'] = wp_create_nonce(
            (new GenerateGatewayRouteUrl())
                ->getNonceActionName($gatewayRouteData)
        );

        $gatewayRouteDTO = GatewayRouteData::fromRequest($gatewayRouteData);

        $this->expectExceptionMessage('The gateway method does not exist');
        $this->gatewayRoute->process($gatewayRouteDTO);
    }

    public function testRedirectOffsiteReturnCallbackCalled()
    {
        give(PaymentGatewayRegister::class)->registerGateway(get_class($this->mockPayPalOffsite));

        $this->mockPayPalOffsite
            ->method('returnSuccessFromOffsiteRedirect')
            ->willThrowException(new InvalidArgumentException('returnSuccessFromOffsiteRedirect function called.'));

        give()->bind(get_class($this->mockPayPalOffsite), function () {
            return $this->mockPayPalOffsite;
        });

        $gatewayRouteData = [
            'give-gateway-id' => $this->mockPayPalOffsite->getId(),
            'give-gateway-method' => 'returnSuccessFromOffsiteRedirect',
            'give-donation-id' => 123,
        ];

        $gatewayRouteData['_wpnonce'] = wp_create_nonce(
            (new GenerateGatewayRouteUrl())
                ->getNonceActionName($gatewayRouteData)
        );

        $gatewayRouteDTO = GatewayRouteData::fromRequest($gatewayRouteData);

        $this->expectExceptionMessage('returnSuccessFromOffsiteRedirect function called.');
        $this->gatewayRoute->process($gatewayRouteDTO);
    }

    public function testGatewayRouteCallbackCalled()
    {
        give(PaymentGatewayRegister::class)->registerGateway(get_class($this->mockPayPalOffsite));

        $this->mockPayPalOffsite
            ->method('handleGatewayRouteMethod')
            ->willThrowException(new InvalidArgumentException('handleGatewayRouteMethod function called.'));

        give()->bind(get_class($this->mockPayPalOffsite), function () {
            return $this->mockPayPalOffsite;
        });

        $gatewayRouteData = [
            'give-gateway-id' => $this->mockPayPalOffsite->getId(),
            'give-gateway-method' => 'handleGatewayRouteMethod',
            'give-donation-id' => 123,
        ];

        $gatewayRouteData['_wpnonce'] = wp_create_nonce(
            (new GenerateGatewayRouteUrl())
                ->getNonceActionName($gatewayRouteData)
        );

        $gatewayRouteDTO = GatewayRouteData::fromRequest($gatewayRouteData);

        $this->expectExceptionMessage('handleGatewayRouteMethod function called.');
        $this->gatewayRoute->process($gatewayRouteDTO);
    }
}
