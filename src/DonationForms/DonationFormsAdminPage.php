<?php

namespace Give\DonationForms;

use Give\Helpers\EnqueueScript;

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

        $data =  [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/forms')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'preload' => $this->preloadForms()
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
     * Make REST request to Donation Forms endpoint before page load
     * @unreleased
     */
    public function preloadForms()
    {
        $queryParameters = [
            'page' => 1,
            'perPage' => 10,
            'status' => 'any',
            'search' => '',
        ];

        $url = add_query_arg(
            $queryParameters,
            $this->apiRoot
        );

        $request = \WP_REST_Request::from_url($url);
        $response = rest_do_request($request);

        return $response->get_data();
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
