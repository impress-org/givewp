<?php

namespace Give\NextGen\DonationForm;

use Give\Helpers\Hooks;
use Give\NextGen\DonationForm\Blocks\DonationFormBlock\Block as DonationFormBlock;
use Give\NextGen\DonationForm\Routes\DonateRoute;
use Give\NextGen\DonationForm\Routes\DonationFormViewRoute;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface {

    /*
     * @inheritdoc
     */
    public function register()
    {
        // TODO: Implement register() method.
    }

    /*
     * @inheritdoc
     */
    public function boot()
    {
        if (function_exists('register_block_type')) {
            Hooks::addAction('init', DonationFormBlock::class, 'register');
        }

        Hooks::addAction('template_redirect', DonateRoute::class);
        Hooks::addAction('template_redirect', DonationFormViewRoute::class);
    }
}
