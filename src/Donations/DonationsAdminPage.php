<?php

namespace Give\Donations;

use Give\Helpers\EnqueueScript;
use WP_REST_Request;

class DonationsAdminPage
{
    /**
     * @var string
     */
    private $apiRoot;

    /**
     * @var string
     */
    private $apiNonce;

    /**
     * @var string
     */
    private $adminUrl;

    public function __construct()
    {
        $this->apiRoot = esc_url_raw(rest_url('give-api/v2/admin/donations'));
        $this->apiNonce = wp_create_nonce('wp_rest');
        $this->adminUrl = admin_url();
    }

    /**
     * @since 2.20.0
     */
    public function registerMenuItem()
    {
        remove_submenu_page(
            'edit.php?post_type=give_forms',
            'give-payment-history'
        );

        remove_action(
            'give_forms_page_give-payment-history',
            'give_payment_history_page'
        );

        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Donations', 'give'),
            esc_html__('Donations', 'give'),
            'edit_give_forms',
            'give-payment-history',
            [$this, 'render'],
            5
        );
    }

    /**
     * @since 2.20.0
     * @since 2.21.2 Localized the admin URL as a base for URL concatenation.
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => $this->apiRoot,
            'apiNonce' => $this->apiNonce,
            'preload' => $this->preloadDonations(),
            'forms' => $this->getForms(),
            'adminUrl' => $this->adminUrl,
        ];

        EnqueueScript::make('give-admin-donations', 'assets/dist/js/give-admin-donations.js')
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveDonations', $data)->enqueue();

        wp_enqueue_style(
            'give-admin-ui-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400..700&display=swap',
            [],
            null
        );
    }

    /**
     * Render admin page container
     * @since 2.20.0
     */
    public function render()
    {
        if (isset($_GET['view']) && 'view-payment-details' === $_GET['view']) {
            include GIVE_PLUGIN_DIR . 'includes/admin/payments/view-payment-details.php';
        } else {
            echo '<div id="give-admin-donations-root"></div>';
        }
    }

    /**
     * Helper function to determine if current page is Give Donors admin page
     * @since 2.20.0
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-payment-history' && !isset($_GET['view']);
    }


    /**
     * Get first page of results from REST API to display as initial table data
     *
     * @since 2.20.0
     * @return array
     */
    private function preloadDonations()
    {
        $queryParameters = [
            'page' => 1,
            'perPage' => 30,
        ];

        if(isset($_GET['search']))
        {
            $queryParameters['search'] = urldecode($_GET['search']);
        }

        $request = WP_REST_Request::from_url(esc_url(add_query_arg(
            $queryParameters,
            $this->apiRoot
        )));

        return rest_do_request($request)->get_data();
    }

    /**
     * Retrieve a list of donation forms to populate the form filter dropdown
     *
     * @since 2.20.0
     * @return array
     */
    private function getForms()
    {
        $queryParameters = [
            'page' => 1,
            'perPage' => 50,
            'status' => 'any'
        ];

        $request = WP_REST_Request::from_url(esc_url_raw(add_query_arg(
            $queryParameters,
            rest_url('give-api/v2/admin/forms')
        )));

        $data = rest_do_request($request)->get_data();

        $options = array_map(static function ($form) {
            return [
                'value' => $form['id'],
                'text' => $form['name'],
            ];
        }, $data['items']);

        return array_merge([
            [
                'value' => '0',
                'text' => 'Any',
            ]
        ], $options);
    }
}
