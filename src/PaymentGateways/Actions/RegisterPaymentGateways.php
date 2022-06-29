<?php

namespace Give\PaymentGateways\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\Gateways\Stripe\Actions\GetPaymentMethodFromRequest;
use Give\PaymentGateways\Gateways\Stripe\BECSGateway as StripeBECSGateway;
use Give\PaymentGateways\Gateways\Stripe\CheckoutGateway as StripeCheckoutGateway;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway as StripeCreditCardGateway;
use Give\PaymentGateways\Gateways\Stripe\SEPAGateway as StripeSEPAGateway;
use Give\PaymentGateways\PayPalCommerce\Actions\GetPayPalOrderFromRequest;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;

class RegisterPaymentGateways
{
    /**
     * Array of Stripe payment method PaymentGateway classes to be bootstrapped
     *
     * @var string[]
     */
    private $stripePaymentMethods = [
        StripeBECSGateway::class,
        StripeCheckoutGateway::class,
        StripeCreditCardGateway::class,
        StripeSEPAGateway::class,
    ];

    /**
     * Array of PaymentGateway classes to be bootstrapped
     *
     * @var string[]
     */
    public $gateways = [
        // When complete, the Test Gateway will eventually replace The legacy Manual Gateway.
        //TestGateway::class,
        //TestGatewayOffsite::class,
        PayPalStandard::class,
        PayPalCommerce::class,
    ];

    /**
     * Registers all the payment gateways with GiveWP
     *
     * @since 2.18.0
     *
     * @param array $gateways
     *
     * @return array
     *
     * @throws InvalidArgumentException|Exception
     *
     */
    public function __invoke(array $gateways): array
    {
        $this->gateways = array_merge(
            $this->gateways,
            $this->stripePaymentMethods
        );

        /** @var PaymentGatewayRegister $paymentGatewayRegister */
        $paymentGatewayRegister = give(PaymentGatewayRegister::class);

        foreach ($this->gateways as $gateway) {
            $paymentGatewayRegister->registerGateway($gateway);
        }

        $this->addGatewayDataToStripPaymentMethods();
        $this->addGatewayDataToPayPalCommerce();

        $this->register3rdPartyPaymentGateways($paymentGatewayRegister);
        $this->unregister3rdPartyPaymentGateways($paymentGatewayRegister);

        return $gateways;
    }

    /**
     * Register 3rd party payment gateways
     *
     * @since 2.21.0 use givewp prefix for action
     * @since 2.18.0
     *
     * @param PaymentGatewayRegister $paymentGatewayRegister
     */
    private function register3rdPartyPaymentGateways(PaymentGatewayRegister $paymentGatewayRegister)
    {
        do_action('givewp_register_payment_gateway', $paymentGatewayRegister);
    }

    /**
     * Unregister 3rd party payment gateways
     *
     * @since 2.21.0 use givewp prefix for action
     * @since 2.18.0
     *
     * @param PaymentGatewayRegister $paymentGatewayRegister
     */
    private function unregister3rdPartyPaymentGateways(PaymentGatewayRegister $paymentGatewayRegister)
    {
        do_action('givewp_unregister_payment_gateway', $paymentGatewayRegister);
    }

    /**
     * @since 2.21.2
     */
    private function addGatewayDataToStripPaymentMethods()
    {
        foreach ($this->stripePaymentMethods as $gatewayClass) {
            add_filter(
                sprintf(
                    'givewp_new_payment_%1$s_gateway_data',
                    $gatewayClass::id()
                ),
                function ($gatewayData, Donation $donation) {
                    return (new GetPaymentMethodFromRequest)($donation);
                },
                10,
                2
            );
        }
    }

    /**
     * @since 2.21.2
     */
    private function addGatewayDataToPayPalCommerce()
    {
        add_filter(
            sprintf(
                'givewp_new_payment_%1$s_gateway_data',
                PayPalCommerce::id()
            ),
            function () {
                return (new GetPayPalOrderFromRequest())();
            }
        );
    }
}
