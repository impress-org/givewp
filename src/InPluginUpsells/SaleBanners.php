<?php

namespace Give\InPluginUpsells;

/**
 * @unreleased
 */
class SaleBanners
{
    /**
     * @var string
     */
    private $optionName = 'hidden-sale-banners';

    /**
     * @var array
     */
    private $hiddenBanners;

    public function __construct()
    {
        $this->hiddenBanners = give_get_option($this->optionName, []);
    }

    /**
     * Get banners definitions
     *
     * @note template maps to src/InPluginUpsells/resources/views/banners/{$template}
     * @note id must be unique for each definition
     *
     * @return array[]
     */
    public function getBanners()
    {
        return [
            [
                'id'        => 'giving_tuesday_2021',
                'template'  => 'giving-tuesday.php',
                'startDate' => strtotime('2021-11-14 00:00'),
                'endDate'   => strtotime('2021-11-17 24:00'),
            ],
            [
                'id'        => 'banner2',
                'template'  => 'banner2.php',
                'startDate' => strtotime('2021-11-17 00:00'),
                'endDate'   => strtotime('2021-11-23 24:00'),
            ]
        ];
    }

    /**
     * @param  array  $banner
     */
    public function renderBanner($banner)
    {
        $currentBanner = $banner[ 'id' ] . get_current_user_id();

        if (in_array($currentBanner, $this->hiddenBanners)) {
            return;
        }

        $currentDateTime = current_datetime();

        if (
            ($currentDateTime->getTimestamp() >= $banner[ 'startDate' ])
            && ($currentDateTime->getTimestamp() <= $banner[ 'endDate' ])
        ) {
            if (file_exists($template = GIVE_PLUGIN_DIR . 'src/InPluginUpsells/resources/views/banners/' . $banner[ 'template' ])) {
                include $template;
            }
        }
    }


    /**
     * @param  string  $banner
     */
    public function hideBanner($banner)
    {
        $this->hiddenBanners[] = $banner;

        give_update_option(
            $this->optionName,
            array_unique($this->hiddenBanners)
        );
    }

    /*
     * Render admin page
     */
    public function render()
    {
        echo '<div class="give-sale-banners-container">';
        foreach ($this->getBanners() as $i => $banner) {
            $this->renderBanner($banner);
        }
        echo '</div>';
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
                'apiRoot'  => esc_url_raw(rest_url('give-api/v2/sale-banner')),
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );
    }

    /**
     * Helper function to determine if the current page Give admin page
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET[ 'post_type' ]) && $_GET[ 'post_type' ] === 'give_forms';
    }
}
