<?php

namespace Give\Promotions\InPluginUpsells;

/**
 * @since 2.17.0
 */
class RecurringDonationsTab
{
    /**
     * Load scripts
     */
    public function loadScripts()
    {
        wp_enqueue_script(
            'give-in-plugin-upsells-recurring-donations',
            GIVE_PLUGIN_URL . 'assets/dist/js/admin-upsell-recurring-donations-settings-tab.js',
            ['wp-element', 'wp-i18n', 'wp-hooks'],
            GIVE_VERSION,
            true
        );

        wp_enqueue_style('givewp-admin-fonts');

        wp_localize_script(
            'give-in-plugin-upsells-recurring-donations',
            'GiveRecurringDonations',
            ['assetsUrl' => GIVE_PLUGIN_URL . 'assets/dist/']
        );
    }

    /**
     * Is the tab active?
     */
    public static function isShowing()
    {
        $recurringIsNotActive = ! defined('GIVE_RECURRING_VERSION');
        $isSettingPage = isset($_GET['page']) && 'give-settings' === $_GET['page'];
        $isRecurringDonationsTab = isset($_GET['tab']) && 'recurring' === $_GET['tab'];

        return $recurringIsNotActive && $isSettingPage && $isRecurringDonationsTab;
    }
}
