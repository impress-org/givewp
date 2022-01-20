<?php

namespace Give\DonationForms;

/**
 * @unreleased
 */
class DonationFormsAdminPage
{
    /**
     * Register menu item
     */
    public function register()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('All Forms', 'give'),
            esc_html__('All Forms', 'give'),
            'edit_give_forms',
            'give-forms',
            [$this, 'render']
        );
    }

    /**
     * Load scripts
     */
    public function loadScripts()
    {
        wp_enqueue_script(
            'give-admin-donation-forms',
            GIVE_PLUGIN_URL . 'assets/dist/js/give-admin-donation-forms.js',
            ['wp-element', 'wp-i18n', 'wp-hooks'],
            GIVE_VERSION,
            true
        );
        wp_localize_script(
            'give-admin-donation-forms',
            'GiveDonationForms',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/forms')),
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );

        wp_enqueue_style(
            'give-admin-ui-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400..700&display=swap',
            [],
            null
        );
    }

    /**
     * Render admin page
     */
    public function render()
    {
        echo '<div id="give-admin-donation-forms-root"></div>';
    }

    /**
     * Helper function to determine if current page is Give Add-ons admin page
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-forms';
    }
}
