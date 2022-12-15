<?php

namespace Give\Promotions\InPluginUpsells;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Give\Framework\Shims\Shim;

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

    /**
     * @since 2.17.0
     */
    public function __construct()
    {
        $this->hiddenBanners = get_option($this->optionName, []);
    }

    /**
     * Get banners definitions
     *
     * @since 2.23.2 add Giving Tuesday 2022 banner
     * @since 2.17.0
     *
     * @note id must be unique for each definition
     */
    public function getBanners(): array
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
            [
                'id' => 'bfgt2022',
                'iconURL' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/sale-icon.png',
                'accessibleLabel' => __('Black Friday/Giving Tuesday Sale', 'give'),
                'leadText' => __('Save 40% on all Plans for a limited time.', 'give'),
                'contentText' => __('Black Friday through Giving Tuesday.', 'give'),
                'actionText' => __('Shop Now', 'give'),
                'actionURL' => 'https://go.givewp.com/bf22',
                'startDate' => '2022-11-01 00:00',
                'endDate' => '2022-11-29 23:59',
            ],
        ];
    }

    /**
     * Get the banners that should be displayed.
     *
     * @since 2.17.0
     */
    public function getVisibleBanners(): array
    {
        $currentDateTime = current_datetime();
        $currentUserId = get_current_user_id();
        $giveWPWebsiteTimezone = new DateTimeZone('America/Los_Angeles');

        return array_filter(
            $this->getBanners(),
            function ($banner) use ($currentDateTime, $currentUserId, $giveWPWebsiteTimezone) {
                $isHidden = in_array($banner['id'] . $currentUserId, $this->hiddenBanners, true);

                try {
                    $isFuture = $currentDateTime < new DateTimeImmutable($banner['startDate'], $giveWPWebsiteTimezone);
                    $isPast = $currentDateTime > new DateTimeImmutable($banner['endDate'], $giveWPWebsiteTimezone);
                } catch(Exception $exception) {
                    return false;
                }

                return !($isHidden || $isFuture || $isPast);
            }
        );
    }

    /**
     * Marks the given banner id as hidden for the current user so it will not display again.
     *
     * @since 2.17.0
     *
     * @return void
     */
    public function hideBanner(string $banner)
    {
        $this->hiddenBanners[] = $banner;

        update_option(
            $this->optionName,
            array_unique($this->hiddenBanners)
        );
    }

    /**
     * Render admin page
     *
     * @since 2.17.0
     */
    public function render()
    {
        $banners = $this->getVisibleBanners();

        if (!empty($banners)) {
            include __DIR__ . '/resources/views/sale-banners.php';
        }
    }

    /**
     * Load scripts
     *
     * @since 2.17.0
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

        wp_enqueue_style('givewp-admin-fonts');
    }

    /**
     * Helper function to determine if the current page Give admin page
     *
     * @since 2.17.0
     */
    public static function isShowing(): bool
    {
        return isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms';
    }
}
