<?php

namespace Give\Promotions\InPluginUpsells;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

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
                        'default' => 'https://go.givewp.com/bfup23',
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
     * @unreleased remove all_access_pass.
     * @since 3.1.0 retrieve licensed plugin slugs.
     */
    public static function getLicensedPluginSlugs(): array
    {
        $pluginSlugs = [];
        $licenses = get_option("give_licenses", []);

        foreach ($licenses as $license) {
            foreach ($license['download'] as $plugin) {
                $pluginSlugs[] = $plugin['plugin_slug'];
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
            'Basic' => self::getBasicLicenseSlugs(),
            'Plus'  => self::getPlusLicenseSlugs(),
            'Pro'   => self::getProLicenseSlugs(),
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
     * @unreleased add type for $data.
     * @since 3.1.0 return data by user pricing plan.
     */
    public static function getDataByPricingPlan(array $data): string
    {
        $userPricingPlan = self::getUserPricingPlan();

        if (array_key_exists($userPricingPlan, $data)) {

            return $data[$userPricingPlan];
        }

        return $data['default'];
    }

    /**
     * @unreleased
     *
     *  This method cycles through the visible banners, selecting the next banner in the list
     *  on each call. If no banners are visible, or if the session index is not set, it returns
     *  all visible banners.
     */
    public function alternateVisibleBanners(): array
    {
        $visibleBanners = $this->getVisibleBanners();
        $bannerCount = count($visibleBanners);

        if ($bannerCount > 0) {
            $currentIndex = $_SESSION['banner_index'] ?? 0;

            $selectedBanner = $visibleBanners[$currentIndex];

            $currentIndex = ($currentIndex + 1) % $bannerCount;

            $_SESSION['banner_index'] = $currentIndex;

            if( !$selectedBanner){
                $this->destroySession();
                return $visibleBanners;
            }

            return [$selectedBanner];
        }

        return $visibleBanners;
    }

    /**
     * @unreleased
     */
    public function startSession(): void
    {
        if (!session_id()) {
            session_start();
        }
    }

    /**
     * @unreleased
     */
    public function destroySession(): void
    {
        if (session_id()) {
            session_destroy();
        }
    }


    /**
     * @unreleased
     */
    public static function getBasicLicenseSlugs(): array
    {
        return [
            'bitpay'              => 'give-bitpay',
            'text_to_give'        => 'give-text-to-give',
            'activecampaign'      => 'give-activecampaign',
            'moneris'             => 'give-moneris',
            'square'              => 'give-square',
            'mollie'              => 'give-mollie',
            'payfast'             => 'give-payfast',
            'sofort'              => 'give-sofort',
            'americloud_payments' => 'give-americloud-payments',
            'paytm'               => 'give-paytm',
            'gocardless'          => 'give-gocardless',
            'razorpay'            => 'give-razorpay',
            'payumoney'           => 'give-payumoney',
            'convertkit'          => 'give-convertkit',
            'aweber'              => 'give-aweber',
            'per_form_gateways'   => 'give-per-form-gateways',
            'email_reports'       => 'give-email-reports',
            'manual_donations'    => 'give-manual-donations',
            'zapier'              => 'give-zapier',
            'google_analytics'    => 'give-google-analytics',
            'ccavenue'            => 'give-ccavenue',
            'constant_contact'    => 'give-constant-contact',
            'braintree'           => 'give-braintree',
            'iats'                => 'give-iats',
            '2checkout'           => 'give-2checkout',
            'pdf_receipts'        => 'give-pdf-receipts',
            'paymill'             => 'give-paymill',
            'stripe'              => 'give-stripe',
            'authorize_net'       => 'give-authorize-net',
            'mailchimp'           => 'give-mailchimp',
        ];
    }

    /**
     * @unreleased
     */
    public static function getPlusLicenseSlugs(): array
    {
        $basicLicenseSlugs = self::getBasicLicenseSlugs();

        $plusLicenseSlugs = [
            'webhooks'                     => 'give-webhooks',
            'salesforce'                   => 'give-salesforce',
            'funds'                        => 'give-funds',
            'annual_receipts'              => 'give-annual-receipts',
            'currency_switcher'            => 'give-currency-switcher',
            'donation_upsells_woocommerce' => 'give-donation-upsells-woocommerce',
            'tributes'                     => 'give-tributes',
            'fee_recovery'                 => 'give-fee-recovery',
            'email_reports'                => 'give-email-reports',
            'gift_aid'                     => 'give-gift-aid',
            'recurring'                    => 'give-recurring',
            'form_field_manager'           => 'give-form-field-manager',
        ];

        return array_merge($basicLicenseSlugs, $plusLicenseSlugs);
    }

    /**
     * @unreleased
     */
    public static function getProLicenseSlugs(): array
    {
        $plusLicenseSlugs = self::getPlusLicenseSlugs();

        $proLicenseSlugs = [
            'peer_to_peer' => 'give-peer-to-peer',
        ];

        return array_merge($plusLicenseSlugs, $proLicenseSlugs);
    }

}

