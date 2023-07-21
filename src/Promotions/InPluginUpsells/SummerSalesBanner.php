<?php

namespace Give\Promotions\InPluginUpsells;

/**
 * @unrleased
 */
class SummerSalesBanner extends SaleBanners
{
    /**
     * @unrleased
     */
    public function getBanners(): array
    {
        $commonBannerInfo = [
            'accessibleLabel' => __('Save 30% on all GiveWP Pricing Plans.', 'give'),
            'leadHeader' => __('Make it yours.', 'give'),
            'contentText' => __(
                'Purchase any StellarWP product during the sale and get 100% off WP Business Reviews and take 40% off all other brands.',
                'give'
            ),
            'actionText' => __('Shop Now', 'give'),
            'alternateActionText' => __('View all StellarWP Deals', 'give'),
            'alternateActionURL' => 'https://go.givewp.com/ss23stellar',
            'startDate' => '2023-07-24 00:00',
            'endDate' => '2023-07-31 23:59',
        ];

        $hasValidLicenses = self::hasValidLicenses();

        if ($hasValidLicenses) {
            return [
                array_merge($commonBannerInfo, [
                    'id' => 'bfgt2023stellar',
                    'leadText' => __('Save 30% on all StellarWP products.', 'give'),
                    'actionURL' => 'https://go.givewp.com/ss23stellar',
                ]),
            ];
        }

        return [
            array_merge($commonBannerInfo, [
                'id' => 'bfgt2023givewp',
                'leadText' => __('Save 30% on all GiveWP Pricing Plans.', 'give'),
                'actionURL' => 'https://go.givewp.com/ss23give',

            ]),
        ];
    }


    /**
     * @unrleased
     */
    public function loadScripts()
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
            'GiveSaleBanners',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/sale-banner')),
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );

        wp_enqueue_style(
            'give-in-plugin-upsells-summer-sales-banner',
            GIVE_PLUGIN_URL . 'assets/dist/css/admin-summer-sales-banner.css',
            [],
            GIVE_VERSION
        );

        wp_enqueue_style('givewp-admin-fonts');

        wp_enqueue_style(
            'Inconsolpata',
            'https://fonts.googleapis.com/css2?family=Inconsolata&display=swap',
            [],
            '1.0',
            'all'
        );
    }

    /**
     * @since 2.31.0
     */
    public function render()
    {
        $banner = $this->getVisibleBanners();

        if ( ! empty($banner)) {
            include __DIR__ . '/resources/views/summer-sales-banner.php';
        }
    }


    /**
     * @since 2.31.0
     */
    public static function isShowing(): bool
    {
        global $pagenow;

        return $pagenow === 'plugins.php';
    }

    /**
     * @since 2.31.0
     */
    public static function hasValidLicenses(): bool
    {
        $requiredPluginSlugs = [
            'recurring' => 'give-recurring',
            'form_field_manager' => 'give-form-field-manager',
            'fee_recovery' => 'give-fee-recovery',
            'manual_donations' => 'give-manual-donations',
            'peer_to_peer' => 'give-peer-to-peer',
        ];

        $licensedPluginSlugs = self::getLicensedPluginSlugs();

        sort($requiredPluginSlugs);
        sort($licensedPluginSlugs);

        return $requiredPluginSlugs == $licensedPluginSlugs;
    }

    /**
     * @since 2.31.0
     */
    public static function getLicensedPluginSlugs(): array
    {
        $pluginSlugs = [];
        $licenses = get_option("give_licenses", []);

        foreach ($licenses as $license) {
            if (isset($license['is_all_access_pass']) && $license['is_all_access_pass'] && ! empty($license['download'])) {
                $slugs = array_column($license['download'], 'plugin_slug');
                $pluginSlugs = array_merge($pluginSlugs, $slugs);
            } else {
                $pluginSlugs[] = $license['plugin_slug'];
            }
        }

        return $pluginSlugs;
    }
}

