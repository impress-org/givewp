<?php

namespace Give\InPluginUpsells;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since 2.17.0
 */
class ServiceProvider implements ServiceProviderInterface
{

    public function register()
    {
    }

    public function boot()
    {
        Hooks::addAction('admin_menu', AddonsAdminPage::class, 'register');
        Hooks::addAction('rest_api_init', HideSaleBannerRoute::class, 'registerRoute');

        if (AddonsAdminPage::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', AddonsAdminPage::class, 'loadScripts');
        }

        if (RecurringDonationsTab::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', RecurringDonationsTab::class, 'loadScripts');
        }

        if (SaleBanners::isShowing()) {
            Hooks::addAction('admin_notices', SaleBanners::class, 'render');
            Hooks::addAction('admin_enqueue_scripts', SaleBanners::class, 'loadScripts');
        }
    }
}
