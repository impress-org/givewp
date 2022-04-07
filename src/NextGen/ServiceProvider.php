<?php

namespace Give\NextGen;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\NextGen\Gateways\TestGatewayNextGen;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        add_action('give_register_payment_gateway', function (PaymentGatewayRegister $registrar) {
            $registrar->registerGateway(TestGatewayNextGen::class);
        });
    }
}
