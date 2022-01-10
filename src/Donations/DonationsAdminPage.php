<?php

namespace Give\Donations;

/**
 * @unreleased
 */
class DonationsAdminPage
{
    /**
     * Register menu item
     */
    public function register()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Donations', 'give'),
            esc_html__('Donations', 'give'),
            'edit_give_payments',
            'give-payment-history',
            [$this, 'render']
        );
    }

    /**
     * Load scripts
     */
    public function loadScripts()
    {
        wp_enqueue_script(
            'give-admin-donations',
            GIVE_PLUGIN_URL . 'assets/dist/js/give-admin-donations.js',
            ['wp-element', 'wp-i18n', 'wp-hooks'],
            GIVE_VERSION,
            true
        );
        wp_localize_script(
            'give-admin-donations',
            'GiveDonations',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/donations')),
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );
    }

    /**
     * Render admin page
     */
    public function render()
    {
        echo '<div id="give-admin-donations-root"></div>';
    }

    /**
     * Helper function to determine if current page is Give Add-ons admin page
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-payment-history';
    }
}
