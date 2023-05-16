<?php

namespace Give\Promotions\InPluginUpsells;

use Give\Helpers\Utils;

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
    public function renderDonationOptionsRecurringRecommendation()
    {
        $isDismissed = get_option('givewp_form_editor_donation_options_recurring_recommendation', false);
        $recurringAddonIsActive = Utils::isPluginActive('give-recurring/give-recurring.php');

        if ($recurringAddonIsActive | $isDismissed) {
            return;
        }

        require_once GIVE_PLUGIN_DIR . 'src/Promotions/InPluginUpsells/resources/views/donation-options-form-editor.php';
    }

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
