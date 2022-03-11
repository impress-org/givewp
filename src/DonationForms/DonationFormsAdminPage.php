<?php

namespace Give\DonationForms;

/**
 * @since 2.19.0
 */
class DonationFormsAdminPage
{
    protected $apiRoot;
    protected $apiNonce;

    public function __construct()
    {
        $this->apiRoot = esc_url_raw(rest_url('give-api/v2/admin/forms'));
        $this->apiNonce = wp_create_nonce('wp_rest');
    }

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
            1
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
                'apiRoot' => $this->apiRoot,
                'apiNonce' => $this->apiNonce,
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
     * Print preload tag for donation forms API request
     * @unreleased
     */
    public function preloadForms()
    {
        $url = add_query_arg(
            [
                '_wpnonce' => $this->apiNonce,
                'page' => 1,
                'perPage' => 10,
                'status' => 'any',
                'search' => ''
            ],
            $this->apiRoot
        );
        printf('<link rel="preload" href="%s" as="fetch" crossorigin="anonymous"/>', $url);
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
