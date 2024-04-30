<?php

namespace Give\Promotions\WelcomeBanner;

class WelcomeBanner
{
    /**
     * @since 3.0.0
     */
    public static function isShowing(): bool
    {
        global $pagenow;

        $option = get_option('givewp_welcome_banner_dismiss');

        return $pagenow === 'plugins.php' && !$option;
    }

    /**
     * @since 3.0.0
     */
    public function render(): void
    {
        echo '<div id="givewp-welcome-banner"></div>';
    }

    /**
     * @since 3.0.0
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
                'action' => 'givewp_welcome_banner_dismiss',
                'assets' => GIVE_PLUGIN_URL . 'assets/dist/images/admin/promotions/welcome-banner',
            ]
        );

        wp_set_script_translations( 'givewp-welcome-banner', 'give' );
        
        wp_enqueue_style('givewp-design-system-foundation');
        wp_enqueue_style('givewp-admin-fonts');
    }
}
