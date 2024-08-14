<?php

/**
 * Onboarding class
 *
 * @package Give
 */

namespace Give\Onboarding\Setup;

use Give\DonationForms\V2\DonationFormsAdminPage;

defined('ABSPATH') || exit;

/**
 * Organizes WordPress actions and helper methods for Onboarding.
 *
 * @since 2.8.0
 */
class Page
{

    const ENABLED = 'enabled';
    const DISABLED = 'disabled';

    /**
     * Dismiss the Setup Page.
     *
     * @since 2.8.0
     */
    public function dismissSetupPage()
    {
        if (wp_verify_nonce($_GET['_wpnonce'], 'dismiss_setup_page')) {
            give_update_option('setup_page_enabled', self::DISABLED);

            wp_redirect(DonationFormsAdminPage::getUrl());
            exit;
        }
    }

    /**
     * Helper method for checking the if the Setup Page is enabled.
     *
     * @since 2.8.0
     *
     * @return string
     */
    public static function getSetupPageEnabledOrDisabled()
    {
        return give_get_option('setup_page_enabled', self::DISABLED);
    }

    /**
     * Add Setup submenu page to admin menu
     *
     * @since 2.8.0
     */
    public function add_page()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Set up GiveWP', 'give'),
            esc_html__('Setup', 'give'),
            'manage_give_settings',
            'give-setup',
            [$this, 'render_page']
        );
    }

    /**
     * Enqueue scripts and styles.
     *
     * @since 2.8.0
     */
    public function enqueue_scripts()
    {
        if (!isset($_GET['page']) || 'give-setup' !== $_GET['page']) {
            return;
        }

        wp_enqueue_style(
            'give-admin-setup-style',
            GIVE_PLUGIN_URL . 'assets/dist/css/admin-setup.css',
            [],
            GIVE_VERSION
        );
        wp_enqueue_style('givewp-admin-fonts');
        wp_enqueue_script(
            'give-admin-setup-script',
            GIVE_PLUGIN_URL . 'assets/dist/js/admin-setup.js',
            ['jquery'],
            GIVE_VERSION,
            $in_footer = true
        );
        
        wp_enqueue_script(
            'give-admin-add-ons-script',
            GIVE_PLUGIN_URL . 'assets/dist/js/admin-add-ons.js',
            ['jquery'],
            GIVE_VERSION,
            $in_footer = true
        );

        $localized_data = [
            'notices' => [
                'invalid_license'        => __( 'Sorry, you entered an invalid key.', 'give' ),
                'download_file'          => __( 'Success! You have activated your license key and are receiving updates and priority support. <a href="{link}">Click here</a> to download your add-on.', 'give' ),
                'addon_activated'        => __( '{pluginName} add-on activated successfully.', 'give' ),
                'addon_activation_error' => __( 'The add-on did not activate successfully.', 'give' ),
            ],
        ];

        wp_localize_script( 'give-admin-add-ons-script', 'give_addon_var', $localized_data );
    }

    /**
     * Render the submenu page
     *
     * @since 2.8.0
     */
    public function render_page()
    {
        $view = give()->make(PageView::class);
        echo $view->render();
    }
}
