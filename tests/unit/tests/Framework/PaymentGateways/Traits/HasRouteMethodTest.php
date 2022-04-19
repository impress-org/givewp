<?php

use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;

/**
 * @unreleased
 */
class HasRouteMethodTest extends WP_UnitTestCase
{
    /**
     * @unreleased
     */
    public function testRegisteredSubscriptionModuleRouteShouldExecute()
    {
        $this->registerGateway();
        $gateway = give(GatewayRouteTestGateway::class);
        $gatewayRouteMethod = 'gatewaySimpleRouteMethod';
        $routeMethod = 'handleSimpleRoute';
        $secureRouteMethod = 'handleSecureRoute';

        $this->assertEquals(
            GatewayRouteTestGateway::class . $gatewayRouteMethod,
            $gateway->callRouteMethod($gatewayRouteMethod, [])
        );

        $this->assertEquals(
            GatewayRouteTestGatewaySubscriptionModule::class . $routeMethod,
            $gateway->callRouteMethod($routeMethod, [])
        );

        $this->assertEquals(
            GatewayRouteTestGatewaySubscriptionModule::class . $secureRouteMethod,
            $gateway->callRouteMethod($secureRouteMethod, [])
        );
    }

    /**
     * @unreleased
     */
    public function testThrowExceptionOnUnRegisteredRouteMethod()
    {
        $this->registerGateway();
        $gateway = give(GatewayRouteTestGateway::class);
        $routeMethod = 'UnRegisteredRoute';

        $this->expectExceptionMessage(
            'UnRegisteredRoute route method is not supported by GatewayRouteTestGateway and GatewayRouteTestGatewaySubscriptionModule'
        );

        $this->assertEquals(
            GatewayRouteTestGatewaySubscriptionModule::class . $routeMethod,
            $gateway->callRouteMethod($routeMethod, [])
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
    public $routeMethods = ['gatewaySimpleRouteMethod'];

    protected function gatewaySimpleRouteMethod($queryParams)
    {
        return __CLASS__ . __FUNCTION__;
    }

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

    public function refundDonation(\Give\Donations\Models\Donation $donation)
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

    protected function handleSimpleRoute($queryParams)
    {
        return __CLASS__ . __FUNCTION__;
    }

    protected function handleSecureRoute($queryParams)
    {
        return __CLASS__ . __FUNCTION__;
    }

    public function cancelSubscription(\Give\Subscriptions\Models\Subscription $subscription)
    {
    }
}
