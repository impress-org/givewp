<?php

namespace Give\Promotions;

use Give\Helpers\Hooks;
use Give\Promotions\FreeAddonModal\Controllers\CompleteRestApiEndpoint;
use Give\Promotions\InPluginUpsells\AddonsAdminPage;
use Give\Promotions\InPluginUpsells\Endpoints\HideSaleBannerRoute;
use Give\Promotions\InPluginUpsells\Endpoints\ProductRecommendationsRoute;
use Give\Promotions\InPluginUpsells\LegacyFormEditor;
use Give\Promotions\InPluginUpsells\PaymentGateways;
use Give\Promotions\InPluginUpsells\SaleBanners;
use Give\Promotions\WelcomeBanner\Endpoints\DismissWelcomeBannerRoute;
use Give\Promotions\WelcomeBanner\WelcomeBanner;
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
     * @since      2.27.1 Removed Recurring donations tab app.
     *
     * Boots the Plugin Upsell promotional page
     *
     * @since      2.19.0
     */
    private function bootPluginUpsells()
    {
        Hooks::addAction('admin_menu', AddonsAdminPage::class, 'register', 70);
        Hooks::addAction('rest_api_init', HideSaleBannerRoute::class, 'registerRoute');
        Hooks::addAction('rest_api_init', ProductRecommendationsRoute::class, 'registerRoute');
        Hooks::addAction('rest_api_init', DismissWelcomeBannerRoute::class, 'registerRoute');

        if (AddonsAdminPage::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', AddonsAdminPage::class, 'loadScripts');
        }

        if (SaleBanners::isShowing()) {
            Hooks::addAction('admin_notices', SaleBanners::class, 'render');
            Hooks::addAction('admin_enqueue_scripts', SaleBanners::class, 'loadScripts');
        }

        if (PaymentGateways::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', PaymentGateways::class, 'loadScripts');
            Hooks::addAction(
                'give_admin_field_enabled_gateways',
                PaymentGateways::class,
                'renderPaymentGatewayRecommendation'
            );
        }

        if (LegacyFormEditor::isShowing()) {
            Hooks::addAction('admin_enqueue_scripts', LegacyFormEditor::class, 'loadScripts');
            Hooks::addAction(
                'give_post_form_field_options_settings',
                LegacyFormEditor::class,
                'renderDonationOptionsRecurringRecommendation'
            );
        }

        if (WelcomeBanner::isShowing()) {
            Hooks::addAction('admin_notices', WelcomeBanner::class, 'render');
            Hooks::addAction('admin_enqueue_scripts', WelcomeBanner::class, 'loadScripts');
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
