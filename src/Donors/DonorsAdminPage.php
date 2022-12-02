<?php

namespace Give\Donors;

use Give\Donors\ListTable\DonorsListTable;
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
     * @var string
     */
    private $adminUrl;

    /**
     * @since 2.20.0
     */
    public function __construct()
    {
        $this->apiRoot = esc_url_raw(rest_url('give-api/v2/admin/donors'));
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
            'give-donors'
        );

        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Donors', 'give'),
            esc_html__('Donors', 'give'),
            'edit_give_forms',
            'give-donors',
            [$this, 'render'],
            5
        );
    }

    /**
     * @since 2.20.0
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => $this->apiRoot,
            'apiNonce' => $this->apiNonce,
            'forms' => $this->getForms(),
            'table' => give(DonorsListTable::class)->toArray(),
            'adminUrl' => $this->adminUrl,
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
     * Preload initial table data
     * @since 2.20.0
     */
    public function getForms(){
        $queryParameters = [
            'page' => 1,
            'perPage' => 50,
            'search' => '',
            'status' => 'any',
            'return' => 'model',
        ];

        $url = esc_url_raw(add_query_arg(
            $queryParameters,
            rest_url('give-api/v2/admin/forms')
        ));

        $request = \WP_REST_Request::from_url($url);
        $response = rest_do_request($request);

        $response = $response->get_data();
        $forms = $response['items'];

        $emptyOption = [
                [
                'value' => '0',
                'text' => 'Any',
            ]
        ];
        $formOptions = array_map(function($form){
            return [
                'value' => $form->id,
                'text' => $form->title,
            ];
        }, $forms);
        return array_merge($emptyOption, $formOptions);
    }

    /**
     * Render admin page container
     * @since 2.20.0
     */
    public function render()
    {
        echo '<div id="give-admin-donors-root"></div>';
    }

    /**
     * Display a button on the old donation forms table that switches to the React view
     *
     * @since 2.20.0
     */
    public function renderReactSwitch()
    {
        ?>
        <script type="text/javascript">
            function showReactTable () {
                fetch( '<?php echo esc_url_raw(rest_url('give-api/v2/admin/donors/view?isLegacy=0')) ?>', {
                    method: 'GET',
                    headers: {
                        ['X-WP-Nonce']: '<?php echo wp_create_nonce('wp_rest') ?>'
                    }
                })
                    .then((res) => {
                        window.location.reload();
                    });
            }
            jQuery( function() {
                jQuery(jQuery(".wrap .wp-header-end")).before(
                    '<button class="page-title-action" onclick="showReactTable()">Switch to New View</button>'
                );
            });
        </script>
        <?php
    }

    /**
     * Helper function to determine if current page is Give Donors admin page
     * @since 2.20.0
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-donors' && !isset($_GET['id']);
    }
}
