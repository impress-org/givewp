<?php

namespace Give\NextGen;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\NextGen\DonationForm\FormTemplates\ClassicFormTemplate\ClassicFormTemplate;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\Framework\FormTemplates\Registrars\FormTemplateRegistrar;
use Give\NextGen\Gateways\NextGenTestGateway\NextGenTestGateway;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\NextGenStripeGateway;
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
        give()->singleton('forms', DonationFormRepository::class);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function boot()
    {
        add_action('givewp_register_payment_gateway', function (PaymentGatewayRegister $registrar) {
            $registrar->registerGateway(NextGenTestGateway::class);
            $registrar->registerGateway(NextGenStripeGateway::class);
        });

        add_action('givewp_register_form_template', function (FormTemplateRegistrar $formTemplateRegistrar) {
            $formTemplateRegistrar->registerTemplate(ClassicFormTemplate::class);
        });
    }
}
