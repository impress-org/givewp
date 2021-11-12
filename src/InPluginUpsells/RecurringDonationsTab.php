<?php

namespace Give\InPluginUpsells;

/**
 * @unreleased
 */
class RecurringDonationsTab {
    protected $containerId = 'give-in-plugin-upsells';

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
     * Render the React container
     */
    public function render() {
        echo '<svg style="display: none"><path id="give-in-plugin-upsells-checkmark" d="M5.595 11.373.72 6.498a.75.75 0 0 1 0-1.06l1.06-1.061a.75.75 0 0 1 1.061 0L6.125 7.66 13.159.627a.75.75 0 0 1 1.06 0l1.061 1.06a.75.75 0 0 1 0 1.061l-8.625 8.625a.75.75 0 0 1-1.06 0Z" fill="currentColor"/></svg>';
		echo "<div id=\"{$this->containerId}\"></div>";
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
