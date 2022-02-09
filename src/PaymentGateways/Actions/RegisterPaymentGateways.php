<?php

namespace Give\PaymentGateways\Actions;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\Gateways\Stripe\BECSGateway as StripeBECSGateway;
use Give\PaymentGateways\Gateways\Stripe\CheckoutGateway as StripeCheckoutGateway;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway as StripeCreditCardGateway;
use Give\PaymentGateways\Gateways\Stripe\SEPAGateway as StripeSEPAGateway;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;

class RegisterPaymentGateways
{
    /**
     * Array of PaymentGateway classes to be bootstrapped
     *
     * @var string[]
     */
    public $gateways = [
        // When complete, the Test Gateway will eventually replace The legacy Manual Gateway.
        //TestGateway::class,
        //TestGatewayOffsite::class,
        StripeBECSGateway::class,
        StripeCheckoutGateway::class,
        StripeCreditCardGateway::class,
        StripeSEPAGateway::class,
        PayPalStandard::class,
        PayPalCommerce::class,
    ];

    /**
     * Registers all the payment gateways with GiveWP
     *
     * @since 2.18.0
     *
     * @param  array  $gateways
     *
     * @return array
     *
     * @throws InvalidArgumentException|Exception
     *
     */
    public function __invoke(array $gateways)
    {
        /** @var PaymentGatewayRegister $paymentGatewayRegister */
        $paymentGatewayRegister = give(PaymentGatewayRegister::class);

        foreach ($this->gateways as $gateway) {
            $paymentGatewayRegister->registerGateway($gateway);
        }

        $this->register3rdPartyPaymentGateways($paymentGatewayRegister);
        $this->unregister3rdPartyPaymentGateways($paymentGatewayRegister);

        return $gateways;
    }

    /**
     * Register 3rd party payment gateways
     *
     * @since 2.18.0
     *
     * @param  PaymentGatewayRegister  $paymentGatewayRegister
     */
    private function register3rdPartyPaymentGateways(PaymentGatewayRegister $paymentGatewayRegister)
    {
        do_action('give_register_payment_gateway', $paymentGatewayRegister);
    }

    /**
     * Unregister 3rd party payment gateways
     *
     * @since 2.18.0
     *
     * @param  PaymentGatewayRegister  $paymentGatewayRegister
     */
    private function unregister3rdPartyPaymentGateways(PaymentGatewayRegister $paymentGatewayRegister)
    {
        do_action('give_unregister_payment_gateway', $paymentGatewayRegister);
    }
}
