<?php

namespace Give\Donations;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{

    public function register()
    {

    }

    public function boot()
    {
        Hooks::addAction('admin_menu', DonationsAdminPage::class, 'register');

        if (DonationsAdminPage::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', DonationsAdminPage::class, 'loadScripts');
        }
    }
}
