<?php

namespace Give\Promotions\WelcomeBanner;

class NextGenWelcomeBanner
{
    /**
     * @unreleased
     */
    public function render(): void
    {
        echo '<div id="givewp-welcome-banner"></div>';
    }

    /**
     * @unreleased
     */
    public function loadScripts(): void
    {
        wp_enqueue_script(
            'givewp-welcome-banner',
            GIVE_PLUGIN_URL . 'assets/dist/js/welcome-banner.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script(
            'givewp-welcome-banner',
            'WelcomeBanner',
            [
                'root' => esc_url_raw(rest_url('give-api/v2/welcome-banner')),
                'nonce' => wp_create_nonce('wp_rest'),
                'action' => 'givewp_next_gen_welcome_release_banner_dismiss',
                'assets' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/promotions/welcome-banner',
            ]
        );

        wp_enqueue_style('givewp-design-system-foundation');
        wp_enqueue_style('givewp-admin-fonts');
    }

    /**
     * @unreleased
     */
    public static function isShowing(): bool
    {
        global $pagenow;

        $option = get_option('givewp_next_gen_welcome_release_banner_dismiss');

        return $pagenow === 'plugins.php' && ! $option;
    }
}
