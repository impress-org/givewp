<?php

namespace Give\Promotions;

use Give\Helpers\Hooks;
use Give\Promotions\FreeAddonModal\Controllers\EnqueueModal;
use Give\Promotions\FreeAddonModal\Controllers\CompleteRestApiEndpoint;
use Give\Promotions\InPluginUpsells\AddonsAdminPage;
use Give\Promotions\InPluginUpsells\HideSaleBannerRoute;
use Give\Promotions\InPluginUpsells\RecurringDonationsTab;
use Give\Promotions\InPluginUpsells\SaleBanners;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;

class ServiceProvider implements ServiceProviderContract
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function boot()
    {
        $this->bootPluginUpsells();
        $this->bootFreeAddonModal();
    }

    /**
     * Boots the Plugin Upsell promotional page
     *
     * @unreleased
     */
    private function bootPluginUpsells() {
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

    /**
     * Boots the free addon modal promotion
     *
     * @unreleased
     */
    private function bootFreeAddonModal()
    {
        Hooks::addAction('admin_enqueue_scripts', EnqueueModal::class, 'enqueueScripts');
        Hooks::addAction('rest_api_init', CompleteRestApiEndpoint::class);
    }
}
