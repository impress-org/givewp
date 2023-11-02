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
     * @since 3.1.0 add Giving Tuesday 2023 banner
     * @since 2.23.2 add Giving Tuesday 2022 banner
     * @since 2.17.0
     *
     * @note id must be unique for each definition
     */
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
                        'Default' => 'https://go.givewp.com/bfup23',
                    ]
                ),
                'startDate' => '2023-11-20 00:00',
                'endDate' => '2023-11-29 23:59',
            ],
        ];
    }

    /**
     * Get the banners that should be displayed.
     *
     * @since 3.1.0 hide banners for users with Pro tier accounts.
     * @since 2.17.0
     */
    public function getVisibleBanners(): array
    {
        if (self::getUserPricingPlan() === 'Pro') {
            return [];
        }

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
                } catch (Exception $exception) {
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

    /**
     * @since 3.1.0 retrieve licensed plugin slugs.
     */
    public static function getLicensedPluginSlugs(): array
    {
        $pluginSlugs = [];
        $licenses = get_option("give_licenses", []);

        foreach ($licenses as $license) {
            if (isset($license['is_all_access_pass']) && $license['is_all_access_pass'] && !empty($license['download'])) {
                $pluginSlugs = ['is_all_access_pass'];
            } else {
                $pluginSlugs[] = $license['plugin_slug'];
            }
        }

        return $pluginSlugs;
    }

    /**
     * @since 3.1.0 determines user pricing plan from licensed plugin slugs.
     */
    public static function getUserPricingPlan(): string
    {
        $plan = 'Free';

        $pricingPlans = [
            'Basic' => ['pdf' => 'give-pdf-receipts'],
            'Plus' => [
                'pdf_receipts' => 'give-pdf-receipts',
                'recurring_donations' => 'give-recurring',
                'fee_recovery' => 'give-fee-recovery',
                'form_field_manager' => 'give-form-field-manager',
                'tributes' => 'give-tributes',
                'annual_receipts' => 'give-annual-receipts',
                'peer_to_peer' => 'give-peer-to-peer',
            ],
            'Pro' => ['is_all_access_pass'],
        ];

        $licensedPluginSlugs = self::getLicensedPluginSlugs();

        foreach ($pricingPlans as $planName => $requiredLicenses) {
            $missingLicenses = array_diff($requiredLicenses, $licensedPluginSlugs);
            if (empty($missingLicenses)) {
                $plan = $planName;
            }
        }

        return $plan;
    }

    /**
     * @since 3.1.0 return data by user pricing plan.
     */
    public static function getDataByPricingPlan($data): string
    {
        $userPricingPlan = self::getUserPricingPlan();

        if (array_key_exists($userPricingPlan, $data)) {
            return $data[$userPricingPlan];
        }

        return $data['default'];
    }
}

