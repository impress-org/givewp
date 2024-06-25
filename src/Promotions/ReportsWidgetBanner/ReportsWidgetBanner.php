<?php

namespace Give\Promotions\ReportsWidgetBanner;

use Give\Promotions\InPluginUpsells\SaleBanners;

/**
* @since 3.13.0
 */
class ReportsWidgetBanner extends SaleBanners
{

    /**
     @since 3.13.0
     */
    public function getBanners(): array
    {
        return [
            [
                'id' => 'bfgt2024-reports-widget',
                'header' => __('Make it yours. Save 40% on all GiveWP products.', 'give'),
                'actionText' => __('Shop Now', 'give'),
                'actionUrl' => 'https://go.givewp.com/40sale24',
                'startDate' => '2024-07-23 00:00',
                'endDate' => '2024-07-30 23:59',
            ],
        ];
    }

    /**
     * @since 3.13.0
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
     * @since 3.13.0
     */
    public static function isShowing(): bool
    {
        $hasBanners = !empty((new ReportsWidgetBanner)->getVisibleBanners());
        $isDashboardWidgetPage = admin_url() . 'index.php' === get_site_url() . $_SERVER['REQUEST_URI'];

        return $hasBanners && $isDashboardWidgetPage;
    }
}
