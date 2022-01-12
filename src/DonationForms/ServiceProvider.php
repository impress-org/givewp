<?php

namespace Give\DonationForms;

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
        Hooks::addAction('admin_menu', DonationFormsAdminPage::class, 'register');

        if (DonationFormsAdminPage::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadScripts');
        }
    }
}
