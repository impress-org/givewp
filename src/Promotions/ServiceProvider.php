<?php

namespace Give\Promotions;

use Give\Helpers\Hooks;
use Give\Promotions\Campaigns\CampaignsWelcomeBanner;
use Give\Promotions\FreeAddonModal\Controllers\CompleteRestApiEndpoint;
use Give\Promotions\InPluginUpsells\AddonsAdminPage;
use Give\Promotions\InPluginUpsells\Endpoints\HideSaleBannerRoute;
use Give\Promotions\InPluginUpsells\Endpoints\ProductRecommendationsRoute;
use Give\Promotions\InPluginUpsells\LegacyFormEditor;
use Give\Promotions\InPluginUpsells\PaymentGateways;
use Give\Promotions\InPluginUpsells\StellarSaleBanners;
use Give\Promotions\ReportsWidgetBanner\ReportsWidgetBanner;
use Give\Promotions\WelcomeBanner\Endpoints\DismissWelcomeBannerRoute;
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
     * @since 4.3.0 refactor to add conditional scripts inside admin_enqueue_scripts hook
     * @since 4.0.0 add CampaignWelcomeBanner
     * @since 3.13.0 add Stellar banner.
     * @since 2.27.1 Removed Recurring donations tab app.
     * @since 2.19.0
     *
     * Boots the Plugin Upsell promotional page
     *
     */
    private function bootPluginUpsells()
    {
        Hooks::addAction('admin_menu', AddonsAdminPage::class, 'register', 70);
        Hooks::addAction('rest_api_init', HideSaleBannerRoute::class, 'registerRoute');
        Hooks::addAction('rest_api_init', ProductRecommendationsRoute::class, 'registerRoute');
        Hooks::addAction('rest_api_init', DismissWelcomeBannerRoute::class, 'registerRoute');

        add_action('admin_enqueue_scripts', static function (){
            if (ReportsWidgetBanner::isShowing()) {
                give(ReportsWidgetBanner::class)->loadScripts();
            }

            if (AddonsAdminPage::isShowing()) {
                give(AddonsAdminPage::class)->loadScripts();
            }

             if (PaymentGateways::isShowing()) {
                 give(PaymentGateways::class)->loadScripts();
            }

             if (LegacyFormEditor::isShowing()) {
                 give(LegacyFormEditor::class)->loadScripts();
            }
        });

         Hooks::addAction(
            'give_admin_field_enabled_gateways',
            PaymentGateways::class,
            'renderPaymentGatewayRecommendation'
        );

          Hooks::addAction(
            'give_post_form_field_options_settings',
            LegacyFormEditor::class,
            'renderDonationOptionsRecurringRecommendation'
        );

        Hooks::addAction('admin_init', CampaignsWelcomeBanner::class);
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
