<?php

use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;

/**
 * @unreleased
 */
class SubscriptionGatewayRouteTest extends WP_UnitTestCase
{
    /**
     * @var PaymentGatewayRegister
     */
    private $registerPaymentGateway;

    /**
     * @unreleased
     */
    public function testDeveloperShouldAbleToRegisterRouteCallback()
    {
        add_filter("give_gateway_GatewayRouteTestGateway_subscription_module", function () {
            return GatewayRouteTestGatewaySubscriptionModule::class;
        });

        $this->registerPaymentGateway = new PaymentGatewayRegister();
        $this->registerPaymentGateway->registerGateway(GatewayRouteTestGateway::class);

        $gateway = give(GatewayRouteTestGateway::class);

        $this->arrayHasKey('handleSimpleRoute', $gateway->routeMethods);
        $this->arrayHasKey('handleSecureRoute', $gateway->routeMethods);
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
