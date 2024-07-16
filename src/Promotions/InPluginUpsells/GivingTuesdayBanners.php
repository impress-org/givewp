<?php

namespace Give\Promotions\InPluginUpsells;

use Give\Log\Log;

class GivingTuesdayBanners extends SaleBanners
{
    public function getBanners(): array
    {
        return [
            [
                'id' => 'bfgt2023',
                'giveIconURL' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/promotions/bfcm-banner/give-logo-icon.svg',
                'discountIconURL' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/promotions/bfcm-banner/discount-icon.svg',
                'backgroundImageLargeURL' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/promotions/bfcm-banner/background-image-lg.svg',
                'backgroundImageMediumURL' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/promotions/bfcm-banner/background-image-md.svg',
                'backgroundImageSmallURL' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/promotions/bfcm-banner/background-image-s.svg',
                'shoppingCartIconURL' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/promotions/bfcm-banner/shopping-cart-icon.svg',
                'dismissIconURL' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/promotions/bfcm-banner/dismiss-icon.svg',
                'accessibleLabel' => __('Black Friday/Giving Tuesday Sale', 'give'),
                'leadText' => self::getDataByPricingPlan(
                    [
                        'Free' => __(
                            'Upgrade to a Pricing Plan for Recurring Donations, Fee Recovery, and more.',
                            'give'
                        ),
                        'Basic' => __(
                            'Upgrade to a Plus Plan to get all must-have add-ons.',
                            'give'
                        ),
                        'Plus' => __(
                            'Upgrade to Pro and get Peer-to-Peer fundraising.',
                            'give'
                        ),
                        'default' => __(
                            'Upgrade to a Pricing Plan for Recurring Donations, Fee Recovery, and more.',
                            'give'
                        ),
                    ]
                ),
                'actionText' => __('Shop Now', 'give'),
                'actionURL' => self::getDataByPricingPlan(
                    [
                        'Free' => 'https://go.givewp.com/bf23',
                        'Basic' => 'https://go.givewp.com/bfup23',
                        'Plus' => 'https://go.givewp.com/bfup23',
                        'default' => 'https://go.givewp.com/bfup23',
                    ]
                ),
                'startDate' => '2023-11-20 00:00',
                'endDate' => '2023-11-29 23:59',
            ],
        ];
    }

    /**
     * Render admin page
     *
     * @since 2.17.0
     */
    public function render(): void
    {
        $banners = $this->getVisibleBanners();

        if (!empty($banners)) {
            include __DIR__ . '/resources/views/sale-banners.php';
        }
    }

    /**
     * Helper function to determine if the current page Give admin page
     *
     * @unreleased only display on the Give forms page and while visible banners are available.
     * @since 2.17.0
     */
    public static function isShowing(): bool
    {
        $saleBanners = new self();

        // Check if the current post type is 'give_forms'
        if (isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms') {
            // Check if there are visible banners
            $visibleBanners = $saleBanners->getVisibleBanners();
            return !empty($visibleBanners);
        }

        return false;
    }
}
