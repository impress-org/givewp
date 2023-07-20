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
