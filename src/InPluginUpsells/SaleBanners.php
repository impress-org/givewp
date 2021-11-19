<?php

namespace Give\InPluginUpsells;

use DateTimeImmutable;
use DateTimeZone;

/**
 * @since 2.17.0
 */
class SaleBanners
{
    /**
     * @var string
     */
    private $optionName = 'give_hidden_sale_banners';

    /**
     * @var array
     */
    private $hiddenBanners;

    public function __construct()
    {
        $this->hiddenBanners = get_option($this->optionName, []);
    }

    /**
     * Get banners definitions
     *
     * @note id must be unique for each definition
     *
     * @return array[]
     */
    public function getBanners()
    {
        return [
            [
                'id' => 'bfgt2021',
                'iconURL' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/sale-icon.png',
                'accessibleLabel' => __('Black Friday/Giving Tuesday Sale', 'give'),
                'leadText' => __('Save 40% on all Plans for a limited time.', 'give'),
                'contentText' => __('Black Friday through Giving Tuesday.', 'give'),
                'actionText' => __('Shop Now', 'give'),
                'actionURL' => 'https://go.givewp.com/bfgt21',
                'startDate' => '2021-11-26 00:00',
                'endDate' => '2021-11-30 23:59',
            ],
        ];
    }

    /**
     * Get the banners that should be displayed.
     *
     * @return array[]
     */
    public function getVisibleBanners()
    {
        $currentDateTime = current_datetime();
        $currentUserId = get_current_user_id();
        $giveWPWebsiteTimezone = new DateTimeZone('America/Los_Angeles');

        return array_filter(
            $this->getBanners(),
            function ($banner) use ($currentDateTime, $currentUserId, $giveWPWebsiteTimezone) {
                $isHidden = in_array($banner['id'] . $currentUserId, $this->hiddenBanners);
                $isFuture = $currentDateTime < new DateTimeImmutable($banner['startDate'], $giveWPWebsiteTimezone);
                $isPast = $currentDateTime > new DateTimeImmutable($banner['endDate'], $giveWPWebsiteTimezone);

                return ! ($isHidden || $isFuture || $isPast);
            }
        );
    }

    /**
     * @param string $banner
     */
    public function hideBanner($banner)
    {
        $this->hiddenBanners[] = $banner;

        update_option(
            $this->optionName,
            array_unique($this->hiddenBanners)
        );
    }

    /**
     * Render admin page
     */
    public function render()
    {
        $banners = $this->getVisibleBanners();

        if ( ! empty($banners)) {
            include __DIR__ . '/resources/views/sale-banners.php';
        }
    }

    /**
     * Load scripts
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
            'give-in-plugin-upsells-sale-banners-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap',
            [],
            null
        );
    }

    /**
     * Helper function to determine if the current page Give admin page
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms';
    }
}
