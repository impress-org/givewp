<?php

namespace Give\DonationForms;

use Give\Helpers\EnqueueScript;

/**
 * @since 2.19.0
 */
class DonationFormsAdminPage
{
    /**
     * Register menu item
     */
    public function register()
    {
        remove_submenu_page('edit.php?post_type=give_forms', 'edit.php?post_type=give_forms');
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Donation Forms', 'give'),
            esc_html__('All Forms', 'give'),
            'edit_give_forms',
            'give-forms',
            [$this, 'render'],
            // Do not change the submenu position unless you have a strong reason.
            // We use this position value to access this menu data in $submenu to add a custom class.
            // Check DonationFormsAdminPage::highlightAllFormsMenuItem
            0
        );
    }

    /**
     * @unreleased
     */
    public function highlightAllFormsMenuItem()
    {
        global $submenu;
        $pages = [
            '/wp-admin/admin.php?page=give-forms', // Donation main menu page.
            '/wp-admin/edit.php?post_type=give_forms' // Legacy donation form listing page.
        ];

        if (in_array($_SERVER['REQUEST_URI'], $pages)) {
            // Add class to highlight 'All Forms' submenu.
            $submenu['edit.php?post_type=give_forms'][0][4] = add_cssclass(
                'current',
                isset($submenu['edit.php?post_type=give_forms'][0][4]) ? $submenu['edit.php?post_type=give_forms'][0][4] : ''
            );
        }
    }

    /**
     * Load scripts
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/forms')),
            'apiNonce' => wp_create_nonce('wp_rest'),
        ];

        EnqueueScript::make('give-admin-donation-forms', 'assets/dist/js/give-admin-donation-forms.js')
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveDonationForms', $data)->enqueue();

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

    /**
     * @unreleased
     * @return string
     */
    public static function getUrl()
    {
        return add_query_arg(['page' => 'give-forms'], admin_url('edit.php?post_type=give_forms'));
    }
}
