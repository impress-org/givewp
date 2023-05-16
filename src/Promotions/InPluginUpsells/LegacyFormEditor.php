<?php

namespace Give\Promotions\InPluginUpsells;

class LegacyFormEditor
{

    /**
     * Load scripts
     *
     * @unreleased
     */
    public function loadScripts()
    {
        wp_enqueue_script(
            'give-in-plugin-upsells-legacy-form-editor',
            GIVE_PLUGIN_URL . 'assets/dist/js/donation-options.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script(
            'give-in-plugin-upsells-legacy-form-editor',
            'GiveLegacyFormEditor',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2')),
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );
    }

    /**
     *
     * @unreleased
     *
     */
//    public function render_donation_options_recurring_recommendation()
//    {
//        if ( ! get_option('givewp_form_editor_donation_options_recurring_recommendation', false)) {
//            require_once GIVE_PLUGIN_DIR . 'src/Promotions/InPluginUpsells/resources/views/donation-options-form-editor.php';
//        }
//
//        add_action('give_post_form_grid_options_settings', 'give_render_donation_options_recurring_recommendation');
//    }

    /**
     *
     * @unreleased
     *
     */
    public static function isShowing(): bool
    {
        $queryParameters = $_GET;

        if (isset($queryParameters['action']) && $queryParameters['action'] === 'edit' && $queryParameters['post']) {
            return true;
        }

        return false;
    }
}
