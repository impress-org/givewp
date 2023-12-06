<?php

namespace Give\PaymentGateways\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayRegisterAdapter;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\PaymentGateways\Gateways\Offline\OfflineGateway;
use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\Gateways\Stripe\Actions\GetPaymentMethodFromRequest;
use Give\PaymentGateways\Gateways\Stripe\BECSGateway as StripeBECSGateway;
use Give\PaymentGateways\Gateways\Stripe\CheckoutGateway as StripeCheckoutGateway;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway as StripeCreditCardGateway;
use Give\PaymentGateways\Gateways\Stripe\SEPAGateway as StripeSEPAGateway;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\PaymentGateways\PayPalCommerce\Exceptions\PayPalOrderException;
use Give\PaymentGateways\PayPalCommerce\Exceptions\PayPalOrderIdException;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;

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
        TestGateway::class,
        PayPalStandard::class,
        OfflineGateway::class,
    ];

    /**
     * Registers all the payment gateways with GiveWP
     *
     * @since 2.25.0 add afterRegisteredGateways
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

        $this->afterRegisteredGateways();

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
                    'givewp_create_payment_gateway_data_%1$s',
                    $gatewayClass::id()
                ),
                function ($gatewayData, Donation $donation) {
                    $gatewayData['stripePaymentMethod'] = (new GetPaymentMethodFromRequest())($donation);

                    return $gatewayData;
                },
                10,
                2
            );
        }
    }

    /**
     * @since 3.2.0 Prevent undefined index notice when getting payPalOrderId from gateway data.
     * @since 2.26.0 Add support for the updated PayPal Commerce gateway data.
     * @since 2.21.2
     *
     * @throws PayPalOrderIdException
     * @throws PayPalOrderException
     */
    private function addGatewayDataToPayPalCommerce()
    {
        add_filter(
            sprintf(
                'givewp_create_payment_gateway_data_%1$s',
                PayPalCommerce::id()
            ),
            function ($gatewayData) {
                if (array_key_exists('payPalOrderId', $gatewayData)) {
                    $paypalOrderId = $gatewayData['payPalOrderId'];
                } else {
                    $paypalOrderId = give_clean($_POST['payPalOrderId']);
                    $gatewayData['payPalOrderId'] = $paypalOrderId;
                }

                if (! $paypalOrderId) {
                    throw new PayPalOrderIdException(__('PayPal order id is missing.', 'give'));
                }

                try {
                    $gatewayData['paypalOrder'] = give(PayPalOrder::class)->getOrder($paypalOrderId);
                } catch (\Exception $e) {
                    throw new PayPalOrderException(__('Unable to get order using order id.', 'give'));
                }

                return $gatewayData;
            }
        );
    }

    /**
     * After gateways have been registered, connect to legacy payment gateway adapter
     */
    private function afterRegisteredGateways()
    {
        /** @var PaymentGatewayRegister $paymentGatewayRegister */
        $paymentGatewayRegister = give(PaymentGatewayRegister::class);

        /** @var LegacyPaymentGatewayRegisterAdapter $legacyPaymentGatewayRegisterAdapter */
        $legacyPaymentGatewayRegisterAdapter = give(LegacyPaymentGatewayRegisterAdapter::class);

        foreach ($paymentGatewayRegister->getPaymentGateways() as $gatewayClass) {
            $legacyPaymentGatewayRegisterAdapter->connectGatewayToLegacyPaymentGatewayAdapter($gatewayClass);
        }
    }
}
