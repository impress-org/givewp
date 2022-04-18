<?php

use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;

/**
 * @unreleased
 */
class SubscriptionGatewayRouteTest extends WP_UnitTestCase
{
    /**
     * @unreleased
     */
    public function testSubscriptionRoutesShouldRegisterToGateway()
    {
        $this->registerGateway();
        $gateway = give(GatewayRouteTestGateway::class);

        $this->assertContains('handleSimpleRoute', $gateway->routeMethods);
        $this->assertContains('handleSecureRoute', $gateway->secureRouteMethods);
    }

    public function testRegisterGatewayRouteShouldExecute()
    {
        $this->registerGateway();
        $gateway = give(GatewayRouteTestGateway::class);
        $methodName = 'handleSimpleRoute';

        $class = new ReflectionClass (GatewayRoute::class);
        $method = $class->getMethod('executeRouteCallback');
        $method->setAccessible(true);
        $actual = $method->invoke(give(GatewayRoute::class), $gateway, $methodName, []);

        $this->assertEquals(
            GatewayRouteTestGatewaySubscriptionModule::class . $methodName,
            $actual
        );
    }

    private function registerGateway()
    {
        add_filter("give_gateway_GatewayRouteTestGateway_subscription_module", function () {
            return GatewayRouteTestGatewaySubscriptionModule::class;
        });

        (new PaymentGatewayRegister())->registerGateway(GatewayRouteTestGateway::class);
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

class GatewayRouteTestGatewaySubscriptionModule extends SubscriptionModule
{
    public $routeMethods = [
        'handleSimpleRoute'
    ];

    public $secureRouteMethods = [
        'handleSecureRoute'
    ];

    public function createSubscription(
        GatewayPaymentData $paymentData,
        GatewaySubscriptionData $subscriptionData
    ) {
    }

    public function handleSimpleRoute()
    {
        return __CLASS__ . __FUNCTION__;
    }

    public function handleSecureRoute()
    {
        return __CLASS__ . __FUNCTION__;
    }
}
