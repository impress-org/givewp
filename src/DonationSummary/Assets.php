<?php

namespace Give\DonationSummary;

class Assets {
    public function loadFrontendAssets() {

        wp_enqueue_style(
            'give-donation-summary-style-frontend',
            GIVE_PLUGIN_URL . 'assets/dist/css/give-donation-summary.css',
            [],
            GIVE_VERSION
        );

        wp_enqueue_script(
            'give-donation-summary-script-frontend',
            GIVE_PLUGIN_URL . 'assets/dist/js/give-donation-summary.js',
            [ 'wp-i18n' ],
            GIVE_VERSION,
            true
        );

        wp_localize_script(
            'give-donation-summary-script-frontend',
            'GiveDonationSummary',
            [
                'foo' => 'bar',
            ]
        );
    }
}
