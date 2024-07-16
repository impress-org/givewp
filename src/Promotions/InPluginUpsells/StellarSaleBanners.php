<?php

namespace Give\Promotions\InPluginUpsells;

/**
 * @since 3.13.0
 */
class StellarSaleBanners extends SaleBanners
{
    /**
     * @since 3.13.0
     */
    public function getBanners(): array
    {
        $banners = [
            [
                'id' => 'bfgt2024-give',
                'mainHeader' => self::getDataByPricingPlan([
                    'Pro' => __('Make it stellar.', 'give'),
                    'default' => __('Make it yours.', 'give'),
                ]),
                'subHeader' => self::getDataByPricingPlan([
                    'Basic' => __('Save 40% on the GiveWP Plus Plan.', 'give'),
                    'Plus' => __('Save 40% on the GiveWP Pro Plan.', 'give'),
                    'Pro' => __('Save 40% on all StellarWP products.', 'give'),
                    'default' => __('Save 40% on the GiveWP Plus Plan.', 'give'),
                ]),
                'actionText' => __('Shop Now', 'give'),
                'actionURL' => self::getDataByPricingPlan([
                    'Basic' => 'https://go.givewp.com/plusplan',
                    'Plus' => 'https://go.givewp.com/pro',
                    'Pro' => 'https://go.givewp.com/stellarsale',
                    'default' => 'https://go.givewp.com/plusplan',
                ]),
                'secondaryActionText' => __('View all StellarWP Deals', 'give'),
                'secondaryActionURL' => 'https://go.givewp.com/stellarsale',
                'content' => self::getDataByPricingPlan([
                    'Pro' => sprintf(__('Take %s off all brands during the annual Stellar Sale. Now through July 30.', 'give'),
                        '<strong>40%</strong>'),
                    'default' => sprintf(__('Take %s off all StellarWP brands during the annual Stellar Sale. Now through July 30.', 'give'),
                        '<strong>40%</strong>'),
                ]),
                'startDate' => '2024-07-23 00:00',
                'endDate' => '2024-07-30 23:59',
            ],
        ];

        foreach($this->getAddonBanners() as $addonBanner){
            $banners[] = $addonBanner;
        }

        return $banners;
    }

    /**
     * @since 3.13.0
     */
    public function getP2PBanners(): array
    {
        return [
            [
                'id' => 'bfgt2024-p2p',
                'mainHeader' => __('Make it yours.', 'give'),
                'subHeader' => __('Save 40% on Peer-to-Peer Fundraising.', 'give'),
                'actionText' => __('Shop Now', 'give'),
                'actionURL' => self::getDataByPricingPlan([
                    'Basic' => 'https://go.givewp.com/p2p',
                    'Plus' => 'https://go.givewp.com/p2ppro',
                    'default' => 'https://go.givewp.com/p2p',
                ]),
                'secondaryActionText' => __('View all StellarWP Deals', 'give'),
                'secondaryActionURL' => 'https://go.givewp.com/stellarsale',
                'content' => self::getDataByPricingPlan([
                    'Basic' => __('Open up your donation forms to your supporters during the annual Stellar Sale. Now through July 30.', 'give'),
                    'Plus' => __('Upgrade to the Pro Plan and get Peer-to-Peer Fundraising during the annual Stellar Sale. Now through July 30.', 'give'),
                    'Pro' => __('Upgrade to the Pro Plan and get Peer-to-Peer Fundraising during the annual Stellar Sale. Now through July 30.', 'give'),
                    'default' => __('Open up your donation forms to your supporters during the annual Stellar Sale. Now through July 30.', 'give'),
                ]),
                'startDate' => '2024-07-23 00:00',
                'endDate' => '2024-07-30 23:59',
            ],
        ];
    }

    /**
     * @since 3.13.0
     */
    public function getAddonBanners(): array
    {
        if(self::getUserPricingPlan() === 'Pro') {
            return [];
        }

        $addonBanners = [];

        if(!defined('GIVE_P2P_VERSION')) {
            $addonBanners = $this->getP2PBanners();
        }

        return $addonBanners;
    }

    /**
     * @since 3.13.0
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
     * @since 3.13.0
     */
    public function render(): void
    {
        $banners = $this->alternateVisibleBanners();

        if (!empty($banners)) {
            include __DIR__ . '/resources/views/stellarwp-sale-banner.php';
        }
    }

    /**
     * @since 3.13.0
     */
    public static function isShowing(): bool
    {
        $saleBanners = new self();
        $page = $_GET['page'] ?? [];
        $validPages = ['give-donors', 'give-payment-history', 'give-reports'];

        return isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms' &&
               in_array($page, $validPages, true) &&
               !empty($saleBanners->getBanners());
    }
}
