<?php

namespace Give\Promotions;

use Give\Helpers\Hooks;
use Give\Promotions\FreeAddonModal\Controllers\DisplaySettingsButton;
use Give\Promotions\FreeAddonModal\Controllers\EnqueueModal;
use Give\Promotions\FreeAddonModal\Controllers\CompleteRestApiEndpoint;
use Give\Promotions\FreeAddonModal\Controllers\PreventFreshInstallPromotion;
use Give\Promotions\InPluginUpsells\AddonsAdminPage;
use Give\Promotions\InPluginUpsells\HideSaleBannerRoute;
use Give\Promotions\InPluginUpsells\RecurringDonationsTab;
use Give\Promotions\InPluginUpsells\SaleBanners;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;

class ServiceProvider implements ServiceProviderContract
{
    /**
     * @since 2.19.0
     *
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @since 2.19.0
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
     * @since 2.19.0
     */
    private function bootPluginUpsells()
    {
        Hooks::addAction('admin_menu', AddonsAdminPage::class, 'register', 70);
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
     * @since 2.19.0
     */
    private function bootFreeAddonModal()
    {
        if (is_admin()) {
            Hooks::addAction('rest_api_init', CompleteRestApiEndpoint::class);
        }
    }
}
