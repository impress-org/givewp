<?php

namespace Give\Promotions\InPluginUpsells;

use Give\Helpers\EnqueueScript;
use Give\Helpers\Utils;

class LegacyFormEditor
{

    /**
     * Load scripts
     *
     * @since 2.27.1
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2')),
            'apiNonce' => wp_create_nonce('wp_rest'),
        ];

        EnqueueScript::make(
            'give-in-plugin-upsells-legacy-form-editor',
            'assets/dist/js/donation-options.js'
        )
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveLegacyFormEditor', $data)
            ->enqueue();
    }

    /**
     *
     * @since 2.27.1
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
     * @since 3.2.1 replaced logic to be give_forms post_type specific
     * @since 2.27.1
     */
    public static function isShowing(): bool
    {
        global $post, $pagenow;

        return $post &&
            in_array($pagenow, ['post-new.php', 'post.php'], true) &&
            'give_forms' === get_post_type($post);
    }
}
