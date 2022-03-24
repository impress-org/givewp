<?php

namespace Give\Donors;

use Give\Helpers\EnqueueScript;

class DonorsAdminPage
{
    /**
     * Root URL for this page's endpoints
     * @var string
     */
    private $apiRoot;

    /**
     * Nonce for authentication with WP REST API
     * @var string
     */
    private $apiNonce;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->apiRoot = esc_url_raw(rest_url('give-api/v2/admin/donors'));
        $this->apiNonce = wp_create_nonce('wp_rest');
    }

    /**
     * @unreleased
     */
    public function registerMenuItem()
    {
        remove_submenu_page(
            'edit.php?post_type=give_forms',
            'give-donors'
        );

        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Donors', 'give'),
            esc_html__('Donors', 'give'),
            'edit_give_forms',
            'give-donors',
            [$this, 'render'],
            6
        );
    }

    /**
     * @unreleased
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => $this->apiRoot,
            'apiNonce' => $this->apiNonce,
            'preload' => $this->preloadDonors(),
        ];

        EnqueueScript::make('give-admin-donors', 'assets/dist/js/give-admin-donors.js')
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveDonors', $data)->enqueue();

        wp_enqueue_style(
            'give-admin-ui-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400..700&display=swap',
            [],
            null
        );
    }

    /**
     * Make REST request to Donors endpoint before page load
     * @unreleased
     */
    public function preloadDonors(){
        $queryParameters = [
            'page' => 1,
            'perPage' => 10,
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
     * Render admin page container
     * @unreleased
     */
    public function render()
    {
        echo '<div id="give-admin-donors-root"></div>';
    }

    /**
     * Helper function to determine if current page is Give Donors admin page
     * @unreleased
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-donors' && !isset($_GET['id']);
    }
}
