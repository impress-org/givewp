<?php

namespace Give\Promotions\InPluginUpsells;

use Give\Log\Log;

/**
 * @unreleased
 */
class StellarSaleBanners extends SaleBanners
{
    /**
     * @unreleased
     */
    public function getBanners(): array
    {
        return [
            [
                'id' => 'bfgt2024-test',
                'mainHeader' => __('Make it yours.', 'give'),
                'subHeader' => __('Save 40% on the GiveWP Plus Plan'),
                'actionText' => __('Shop Now', 'give'),
                'actionURL' => 'https://www.actionURL.com',
                'secondaryActionText' => __('View all StellarWP Deals', 'give'),
                'secondaryActionURL' => 'https://www.secondaryActionURL.com',
                'content' => __('Take 40% off all StellarWP brands during the annual Stellar Sale. Now through July 30.', 'give'),
                'startDate' => '2024-06-05 00:00',
                'endDate' => '2024-06-10 23:59',
            ],
        ];
    }

    /**
     * @unreleased
     */
    public function loadScripts(): void
    {
        wp_enqueue_style(
            'give-in-plugin-upsells-stellar-sales-banner',
            GIVE_PLUGIN_URL . 'assets/dist/css/admin-stellarwp-sales-banner.css',
            [],
            GIVE_VERSION
        );

        wp_enqueue_style('givewp-admin-fonts');
    }

    /**
     * @unreleased
     */
    public function render(): void
    {
        $banners = $this->getVisibleBanners();

        if (!empty($banners)) {
            include __DIR__ . '/resources/views/stellarwp-sale-banner.php';
        }
    }

}
