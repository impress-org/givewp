<?php

namespace Give\NextGen;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\NextGen\DonationForm\FormDesigns\ClassicFormDesign\ClassicFormDesign;
use Give\NextGen\DonationForm\FormDesigns\DeveloperFormDesign\DeveloperFormDesign;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\NextGen\Gateways\NextGenTestGateway\NextGenTestGateway;
use Give\NextGen\Gateways\NextGenTestGatewayOffsite\NextGenTestGatewayOffsite;
use Give\NextGen\Gateways\PayPal\PayPalStandardGateway\PayPalStandardGateway;
use Give\NextGen\Gateways\PayPalCommerce\PayPalCommerceGateway;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\NextGenStripeGateway;
use Give\PaymentGateways\Gateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 0.1.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('forms', DonationFormRepository::class);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function boot()
    {
        add_action('givewp_register_payment_gateway', static function (PaymentGatewayRegister $registrar) {
            $registrar->registerGateway(NextGenTestGateway::class);
            $registrar->registerGateway(NextGenStripeGateway::class);
            $registrar->registerGateway(NextGenTestGatewayOffsite::class);
            $registrar->unregisterGateway(PayPalStandard::id());
            $registrar->registerGateway(PayPalStandardGateway::class);

            $registrar->unregisterGateway(PayPalCommerce::id());
            $registrar->registerGateway(PayPalCommerceGateway::class);
        });


        add_filter("givewp_create_payment_gateway_data_" . PayPalCommerce::id(), function ($gatewayData) {
            //
            $gatewayData['payPalOrderId'] = $gatewayData['payPalOrderId'] ?? give_clean($_POST['payPalOrderId']);
            return $gatewayData;
        });

        add_action('givewp_register_form_design', static function (FormDesignRegistrar $formDesignRegistrar) {
            $formDesignRegistrar->registerDesign(ClassicFormDesign::class);
            $formDesignRegistrar->registerDesign(DeveloperFormDesign::class);
        });
    }
}
