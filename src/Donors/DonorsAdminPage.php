<?php

namespace Give\Donors;

use Give\Donors\ListTable\DonorsListTable;
use Give\Framework\Database\DB;
use Give\Helpers\EnqueueScript;
use Give\Helpers\Utils;

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
            [$this, 'render']
        );
    }

    /**
     * @since 2.27.1 Pass dissmissedRecommendations
     *
     * @since      2.20.0
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => $this->apiRoot,
            'apiNonce' => $this->apiNonce,
            'forms' => $this->getForms(),
            'table' => give(DonorsListTable::class)->toArray(),
            'adminUrl' => $this->adminUrl,
            'pluginUrl' => GIVE_PLUGIN_URL,
            'dismissedRecommendations' => $this->getDismissedRecommendations(),
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
    public function getForms()
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
                'text' => __('Any', 'give'),
            ],
        ], $options);
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
            function showReactTable() {
                fetch('<?php echo esc_url_raw(rest_url('give-api/v2/admin/donors/view?isLegacy=0')) ?>', {
                    method: 'GET',
                    headers: {
                        ['X-WP-Nonce']: '<?php echo wp_create_nonce('wp_rest') ?>',
                    },
                })
                    .then((res) => {
                        window.location.reload();
                    });
            }

            jQuery(function () {
                jQuery(jQuery(".wrap .wp-header-end")).before(
                    '<button class="page-title-action" onclick="showReactTable()"><?php _e('Switch to New View', 'give') ?></button>',
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
        return isset($_GET['page']) && $_GET['page'] === 'give-donors' && ! isset($_GET['id']);
    }

    /**
     * Retrieve a list of dismissed recommendations.
     *
     * @since 2.27.1
     *
     * @return array
     */
    private function getDismissedRecommendations(): array
    {
        $dismissedRecommendations = [];

        $feeRecoveryAddonIsActive = Utils::isPluginActive('give-fee-recovery/give-fee-recovery.php');

        $optionName = 'givewp_donors_fee_recovery_recommendation_dismissed';
        
        $dismissed = get_option($optionName, false);

        if ($dismissed || $feeRecoveryAddonIsActive) {
            $dismissedRecommendations[] = $optionName;
        }

        return $dismissedRecommendations;
    }

}
