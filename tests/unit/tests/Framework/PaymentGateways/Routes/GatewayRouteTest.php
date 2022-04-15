<?php

use Give\Framework\PaymentGateways\Contracts\SubscriptionModuleInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;

/**
 * @unknown
 */
class GatewayRouteTest extends WP_UnitTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->registerPaymentGateway = new PaymentGatewayRegister();
        $this->registerPaymentGateway->registerGateway(GatewayRouteTestGateway::class);
    }

    public function testDeveloperShouldAbleToRegisterRouteCallback()
    {
        $gateway = give(GatewayRouteTestGateway::class);
        $methodName = 'handleStripeCreditCardSCA';
        $callbackData = [GatewayRouteTestGatewaySubscriptionModule::class, $methodName];

        $gateway->register3rdPartyRouteMethod($methodName, GatewayRouteTestGatewaySubscriptionModule::class);

        $arrayDiff = array_diff($gateway->routeMethods[$methodName], $callbackData);
        $this->assertCount(0, $arrayDiff);
    }

    public function testDeveloperShouldAbleToRegisterSecureRouteCallback()
    {
        $gateway = give(GatewayRouteTestGateway::class);
        $methodName = 'handleStripeCreditCardSCA';
        $callbackData = [GatewayRouteTestGatewaySubscriptionModule::class, $methodName];

        $gateway->deRegister3rdPartyRouteMethod($methodName);
        $gateway->register3rdPartyRouteMethod($methodName, GatewayRouteTestGatewaySubscriptionModule::class, true);

        $arrayDiff = array_diff($gateway->secureRouteMethods[$methodName], $callbackData);
        $this->assertCount(0, $arrayDiff);
    }

    public function testRegister3rdPartyGatewayRouteShouldExecute()
    {
        $gateway = give(GatewayRouteTestGateway::class);
        $methodName = 'handleStripeCreditCardSCA';

        $gateway->register3rdPartyRouteMethod($methodName, GatewayRouteTestGatewaySubscriptionModule::class);

        $class = new ReflectionClass (GatewayRoute::class);
        $method = $class->getMethod('executeRouteCallback');
        $method->setAccessible(true);
        $actual = $method->invoke(give(GatewayRoute::class), $gateway, $methodName, []);

        $this->assertEquals(
            GatewayRouteTestGatewaySubscriptionModule::class . $methodName,
            $actual
        );
    }

    public function testExecute3rdRouteRegisterFromAllowedSource()
    {
        $this->expectErrorMessage(
            sprintf(
                'Gateway route registered from %1$s class is not processable.',
                GatewayRouteTestGatewayTransactionModule::class
            )
        );
        $gateway = give(GatewayRouteTestGateway::class);
        $methodName = 'handleStripeCreditCardSCA';

        $gateway->register3rdPartyRouteMethod($methodName, GatewayRouteTestGatewayTransactionModule::class);

        $class = new ReflectionClass (GatewayRoute::class);
        $method = $class->getMethod('executeRouteCallback');
        $method->setAccessible(true);
        $actual = $method->invoke(give(GatewayRoute::class), $gateway, $methodName, []);

        $this->assertEquals(
            GatewayRouteTestGatewayTransactionModule::class . $methodName,
            $actual
        );
    }
}

class GatewayRouteTestGateway extends PaymentGateway
{
    public function getLegacyFormFieldMarkup($formId, $args)
    {
        return '';
    }

    public static function id()
    {
        return 'GatewayRouteTestGateway';
    }

    public function getId()
    {
        return self::id();
    }

    public function getName()
    {
        return self::id();
    }

    public function getPaymentMethodLabel()
    {
        return self::id();
    }

    public function createPayment(GatewayPaymentData $paymentData)
    {
    }
}

class GatewayRouteTestGatewaySubscriptionModule implements SubscriptionModuleInterface
{
    public function createSubscription(
        GatewayPaymentData $paymentData,
        GatewaySubscriptionData $subscriptionData
    ) {
    }

    public function handleStripeCreditCardSCA()
    {
        return __CLASS__ . __FUNCTION__;
    }
}

class GatewayRouteTestGatewayTransactionModule
{
    public function handleStripeCreditCardSCA()
    {
        return __CLASS__ . __FUNCTION__;
    }
}
