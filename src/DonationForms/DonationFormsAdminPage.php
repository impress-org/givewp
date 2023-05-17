<?php

namespace Give\DonationForms;

use Give\DonationForms\ListTable\DonationFormsListTable;
use Give\Helpers\EnqueueScript;
use WP_REST_Request;

/**
 * @since 2.19.0
 */
class DonationFormsAdminPage
{
    /**
     * @var string
     */
    protected $apiRoot;
    /**
     * @var string
     */
    protected $apiNonce;
    /**
     * @var string
     */
    protected $adminUrl;

    public function __construct()
    {
        $this->apiRoot = esc_url_raw(rest_url('give-api/v2/admin/forms'));
        $this->apiNonce = wp_create_nonce('wp_rest');
        $this->adminUrl = admin_url();
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
            // Do not change the submenu position unless you have a strong reason.
            // We use this position value to access this menu data in $submenu to add a custom class.
            // Check DonationFormsAdminPage::highlightAllFormsMenuItem
            0
        );
    }

    /**
     * @since 2.20.0
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
        $data =  [
            'apiRoot' => $this->apiRoot,
            'apiNonce' => $this->apiNonce,
            'preload' => $this->preloadDonationForms(),
            'authors' => $this->getAuthors(),
            'table' => give(DonationFormsListTable::class)->toArray(),
            'adminUrl' => $this->adminUrl,
            'pluginUrl' => GIVE_PLUGIN_URL,
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
     * Get first page of results from REST API to display as initial table data
     *
     * @since 2.20.0
     * @return array
     */
    private function preloadDonationForms()
    {
        $queryParameters = [
            'page' => 1,
            'perPage' => 30,
        ];

        $request = WP_REST_Request::from_url(add_query_arg(
            $queryParameters,
            $this->apiRoot
        ));

        return rest_do_request($request)->get_data();
    }

    /**
     * Get a list of author user IDs and names
     * @since 2.20.0
     */
    public function getAuthors()
    {
        $author_users = get_users([
            'role__in'  => ['author', 'administrator']
        ]);
        return array_map(function($user){
            return [
                'id'    => $user->ID,
                'name'  => $user->display_name,
            ];
        }, $author_users);
    }

    /**
     * Render admin page
     */
    public function render()
    {
        echo '<div id="give-admin-donation-forms-root"></div>';
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
                fetch( '<?php echo esc_url_raw(rest_url('give-api/v2/admin/forms/view?isLegacy=0')) ?>', {
                    method: 'GET',
                    headers: {
                        ['X-WP-Nonce']: '<?php echo wp_create_nonce('wp_rest') ?>'
                    }
                })
                    .then((res) => {
                        window.location = window.location.href = '/wp-admin/edit.php?post_type=give_forms&page=give-forms';
                    });
            }
            jQuery( function() {
                jQuery(jQuery(".wrap .page-title-action")[0]).after(
                    '<button class="page-title-action" onclick="showReactTable()">Switch to New View</button>'
                );
            });
        </script>
        <?php
    }

    /**
     * Helper function to determine if current page is Give Add-ons admin page
     *
     * @since 2.20.0
     */
    public static function isShowing(): bool
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-forms';
    }

    /**
     * Helper function to determine if the current page is the legacy donation forms list page
     *
     * @since 2.20.1
     */
    public static function isShowingLegacyPage(): bool
    {
        return isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms' && empty($_GET['page']);
    }

    /**
     * @since 2.20.0
     * @return string
     */
    public static function getUrl()
    {
        return add_query_arg(['page' => 'give-forms'], admin_url('edit.php?post_type=give_forms'));
    }
}
