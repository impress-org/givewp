<?php

namespace Give\Promotions\InPluginUpsells;

use Give\Framework\Shims\Shim;

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
            'accessibleLabel' => __('Black Friday/Giving Tuesday Sale', 'give'),
            'leadHeader' => __('Make it yours.', 'give'),
            'contentText' => __(
                'Purchase any StellarWP product during the sale and get 100% off WP Business Reviews and take 40% off all other brands.',
                'give'
            ),
            'actionText' => __('Shop Now', 'give'),
            'alternateActionText' => __('View all StellarWP Deals', 'give'),
            'actionURL' => 'https://go.givewp.com/ss23give',
            'alternateActionURL' => 'https://go.givewp.com/ss23stellar',
            'startDate' => '2023-07-16 00:00',
            'endDate' => '2023-07-19 23:59',
        ];

        $has_valid_license = self::hasValidLicenses();

        if ($has_valid_license) {
            return [
                array_merge($commonBannerInfo, [
                    'id' => 'bfgt2023stellar',
                    'leadText' => __('Save 30% on all GiveWP Pricing Plans.', 'give'),
                ]),
            ];
        }

        return [
            array_merge($commonBannerInfo, [
                'id' => 'bfgt2023givewp',
                'leadText' => __('Save 30% on all StellarWP products.', 'give'),
            ]),
        ];
    }


    /**
     * @unrleased
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
            'GiveSaleBanners',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/sale-banner')),
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );

        wp_enqueue_style(
            'give-in-plugin-upsells-summer-sales-banner',
            GIVE_PLUGIN_URL . 'assets/dist/css/admin-summer-sales-banner.css',
            '1.0.0',
        );

        wp_enqueue_style('givewp-admin-fonts');
    }

    /**
     * @unreleased
     */
    public function render(): void
    {
        $banners = $this->getVisibleBanners();

        if ( ! empty($banners)) {
            include __DIR__ . '/resources/views/summer-sales-banner.php';
        }
    }


    /**
     * @unreleased
     */
    public static function isShowing(): bool
    {
        global $pagenow;

        return $pagenow === 'plugins.php';
    }

    /**
     * @unreleased
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


        return sort($requiredPluginSlugs) === sort(self::getAllExistingLicenseSlugs());
    }

    /**
     * @unreleased
     */
    public static function getAllExistingLicenseSlugs(): array
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

