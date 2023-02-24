<?php

namespace Give\Subscriptions;

use Give\Framework\Database\DB;
use Give\Helpers\EnqueueScript;
use Give\Subscriptions\ListTable\SubscriptionsListTable;

class SubscriptionsAdminPage
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
        $this->apiRoot = esc_url_raw(rest_url('give-api/v2/admin/subscriptions'));
        $this->apiNonce = wp_create_nonce('wp_rest');
        $this->adminUrl = admin_url();
    }

    /**
     * @since 2.24.0
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => $this->apiRoot,
            'apiNonce' => $this->apiNonce,
            'forms' => $this->getForms(),
            'table' => give(SubscriptionsListTable::class)->toArray(),
            'adminUrl' => $this->adminUrl,
            'paymentMode' => give_is_test_mode(),
            'pluginUrl' => GIVE_PLUGIN_URL
        ];

        EnqueueScript::make('give-admin-subscriptions', 'assets/dist/js/give-admin-subscriptions.js')
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveSubscriptions', $data)->enqueue();

        wp_enqueue_style(
            'give-admin-ui-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400..700&display=swap',
            [],
            null
        );
    }

    /**
     * Retrieve a list of donation forms to populate the form filter dropdown
     *
     * @since 2.24.0
     *
     * @return array
     */
    private function getForms()
    {
        $options = DB::table('posts')
            ->select(
                ['ID', 'value'],
                ['post_title', 'text']
            )
            ->where('post_type', 'give_forms')
            ->whereIn('post_status', ['publish', 'draft', 'pending', 'private'])
            ->getAll(ARRAY_A);

        return array_merge([
            [
                'value' => '0',
                'text' => 'Any',
            ]
        ], $options);
    }

    /**
     * Display a button on the old subscriptions table that switches to the React view
     *
     * @since 2.24.0
     */
    public function renderReactSwitch()
    {
        ?>
        <script type="text/javascript">
            function showReactTable () {
                fetch( '<?php echo esc_url_raw(rest_url('give-api/v2/admin/subscriptions/view?isLegacy=0')) ?>', {
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
     * Helper function to determine if current page is Give Subscriptions admin page
     *
     * @since 2.24.0
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-subscriptions' && !isset($_GET['view']);
    }
}
