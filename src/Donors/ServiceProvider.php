<?php

namespace Give\Donors;

use Give\Donors\Repositories\DonorRepository;
use Give\Helpers\Hooks;
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
        give()->singleton('donorRepository', DonorRepository::class);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('admin_menu', DonorsAdminPage::class, 'registerMenuItem');

        if (DonorsAdminPage::isShowing()) {
            // Disabled until actual file is created
            // Hooks::addAction('admin_enqueue_scripts', DonorsAdminPage::class, 'loadScripts');
        }
    }
}
