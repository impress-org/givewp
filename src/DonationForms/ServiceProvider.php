<?php

namespace Give\DonationForms;

use Give\DonationForms\Repositories\DonationFormsRepository;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 2.19.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donationFormsRepository', DonationFormsRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('admin_menu', DonationFormsAdminPage::class, 'register');

        if (DonationFormsAdminPage::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', DonationFormsAdminPage::class, 'loadScripts');
        }
    }
}
