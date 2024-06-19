<?php

namespace Give\Promotions\ReportsWidgetBanner;

use Give\Promotions\InPluginUpsells\SaleBanners;

/**
* @unreleased
 */
class ReportsWidgetBanner extends SaleBanners
{

    /**
     @unreleased
     */
    public function getBanners(): array
    {
        return [
            [
                'id' => 'bfgt2024-reports-widget',
                'header' => __('Make it yours. Save 40% on all GiveWP products.', 'give'),
                'actionText' => __('Shop Now', 'give'),
                'actionUrl' => 'www.test.com',
                'startDate' => '2024-06-12 00:00',
                'endDate' => '2024-06-30 23:59',
            ],
        ];
    }

    /**
     * @unreleased
     */
    public function loadScripts(): void
    {
        wp_enqueue_script(
            'give-in-plugin-upsells-sale-banners',
            GIVE_PLUGIN_URL . 'assets/dist/js/admin-upsell-sale-banner.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script(
            'give-in-plugin-upsells-sale-banners',
            'giveReportsWidget',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/sale-banner')),
                'apiNonce' => wp_create_nonce('wp_rest'),
                'banner' => $this->getVisibleBanners()[0],
            ]
        );
    }

    /**
     * @unreleased
     */
    public static function isShowing(): bool
    {
        $hasBanners = !empty((new ReportsWidgetBanner)->getVisibleBanners());
        $isDashboardWidgetPage = admin_url() . 'index.php' === get_site_url() . $_SERVER['REQUEST_URI'];

        return $hasBanners && $isDashboardWidgetPage;
    }
}
