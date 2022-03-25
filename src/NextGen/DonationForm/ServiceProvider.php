<?php

namespace Give\NextGen\DonationForm;

use Give\Helpers\Hooks;
use Give\NextGen\DonationForm\Blocks\DonationFormBlock\Bootstrap\RegisterDonationFormBlock;
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
            Hooks::addAction('init', RegisterDonationFormBlock::class);
        }
    }
}
