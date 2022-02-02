<?php

namespace Give\DonationForms;

use Give\DonationForms\Repositories\DonationFormsRepository;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
{

    public function register()
    {
        give()->singleton('donationFormsRepository', DonationFormsRepository::class);
    }

    public function boot()
    {
        Hooks::addAction('admin_menu', DonationFormsAdminPage::class, 'register');

        if (DonationFormsAdminPage::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadScripts');
        }
    }
}
