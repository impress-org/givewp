<?php

namespace Give\NextGen\Framework\FormDesigns;

use Give\Helpers\Hooks;
use Give\NextGen\Framework\FormDesigns\Actions\RegisterFormDesigns;
use Give\NextGen\Framework\FormDesigns\Registrars\FormDesignRegistrar;
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
        give()->singleton(FormDesignRegistrar::class, function () {
            $registrar = new FormDesignRegistrar();

            Hooks::doAction('givewp_register_form_design', $registrar);
            Hooks::doAction('givewp_unregister_form_design', $registrar);

            return $registrar;
        });
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        //
    }
}
