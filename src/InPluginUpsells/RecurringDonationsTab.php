<?php

namespace Give\InPluginUpsells;

/**
 * @unreleased
 */
class RecurringDonationsTab {
    /**
     * Load scripts
     */
    public function loadScripts() {
        wp_enqueue_script(
            'give-in-plugin-upsells-recurring-donations',
            GIVE_PLUGIN_URL . 'assets/dist/js/admin-upsell-recurring-donations-tab.js',
            ['wp-element', 'wp-i18n', 'wp-hooks'],
            GIVE_VERSION,
            true
        );

		wp_enqueue_style(
			'give-in-plugin-upsells-addons-font',
			'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap',
			[],
			null
		);

		wp_localize_script(
			'give-in-plugin-upsells-addons',
			'GiveRecurringDonations',
            ['assetsUrl' => GIVE_PLUGIN_URL . 'assets/dist/']
        );
    }

    /**
     * Is the tab active?
     */
    public static function isShowing() {
        $isSettingPage = isset( $_GET['page'] ) && 'give-settings' === $_GET['page'];
        $isRecurringDonationsTab = isset( $_GET['tab'] ) && 'recurring-donations' === $_GET['tab'];

        return $isSettingPage && $isRecurringDonationsTab;
    }
}
