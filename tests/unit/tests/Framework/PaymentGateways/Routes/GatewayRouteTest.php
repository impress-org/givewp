<?php

use Give\Framework\PaymentGateways\Actions\GenerateGatewayRouteUrl;
use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * @unreleased
 */
class GatewayRouteTest extends Give_Unit_Test_Case
{
    /**
     * @var GatewayRoute
     */
    private $gatewayRoute;

    public function setUp()
    {
        parent::setUp();

        $this->gatewayRoute = new GatewayRoute();

        give(PaymentGatewayRegister::class)
            ->registerGateway(MockPaypalOffsiteForGatewayRouteTest::class);

        $this->mock(
            MockPaypalOffsiteForGatewayRouteTest::class,
            function (PHPUnit_Framework_MockObject_MockBuilder $mockBuilder) {
                $mock = $mockBuilder
                    ->setMethods(['returnSuccessFromOffsiteRedirect', 'handleIpnNotification'])
                    ->getMock();

                $mock->method('returnSuccessFromOffsiteRedirect')
                     ->willThrowException(
                         new InvalidArgumentException('returnSuccessFromOffsiteRedirect function called.')
                     );

                $mock->method('handleIpnNotification')
                     ->willThrowException(
                         new InvalidArgumentException('handleIpnNotification function called.')
                     );

                return $mock;
            }
        );
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        // De register gateway.
        give(PaymentGatewayRegister::class)
            ->unregisterGateway(MockPaypalOffsiteForGatewayRouteTest::id());

        parent::tearDown();
    }

    public function testThrowExceptionOnInvalidGatewayId()
    {
        $gatewayRouteData = GatewayRouteData::fromRequest(
            [
                'give-gateway-id' => 'thisGatewayRouteISNotRegistered',
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
        $gatewayRouteData = GatewayRouteData::fromRequest(
            [
                'give-gateway-id' => MockPaypalOffsiteForGatewayRouteTest::id(),
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
        $gatewayRouteData = [
            'give-gateway-id' => MockPaypalOffsiteForGatewayRouteTest::id(),
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
        $gatewayRouteData = [
            'give-gateway-id' => MockPaypalOffsiteForGatewayRouteTest::id(),
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
        $gatewayRouteData = [
            'give-gateway-id' => MockPaypalOffsiteForGatewayRouteTest::id(),
            'give-gateway-method' => 'handleIpnNotification',
            'give-donation-id' => 123,
        ];

        $gatewayRouteData['_wpnonce'] = wp_create_nonce(
            (new GenerateGatewayRouteUrl())
                ->getNonceActionName($gatewayRouteData)
        );

        $gatewayRouteDTO = GatewayRouteData::fromRequest($gatewayRouteData);

        $this->expectExceptionMessage('handleIpnNotification function called.');
        $this->gatewayRoute->process($gatewayRouteDTO);
    }
}

class MockPaypalOffsiteForGatewayRouteTest extends OffSitePaymentGateway
{
    public $routeMethods = ['handleIpnNotification'];

    public function handleIpnNotification()
    {
    }

    public function getLegacyFormFieldMarkup($formId, $args)
    {
        // TODO: Implement getLegacyFormFieldMarkup() method.
    }

    public static function id()
    {
        return 'mock-offsite-paypal';
    }

    public function getId()
    {
        return self::id();
    }

    public function getName()
    {
        // TODO: Implement getName() method.
    }

    public function getPaymentMethodLabel()
    {
        // TODO: Implement getPaymentMethodLabel() method.
    }

    public function createPayment(GatewayPaymentData $paymentData)
    {
        // TODO: Implement createPayment() method.
    }
}
