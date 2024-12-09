<?php

namespace Give\DonationForms\V2;

use Give\DonationForms\V2\ListTable\DonationFormsListTable;
use Give\FeatureFlags\OptionBasedFormEditor\OptionBasedFormEditor;
use Give\Helpers\EnqueueScript;
use WP_Post;
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
    /**
     * @var string
     */
    private $bannerActionUrl;
    /**
     * @var string
     */
    private $tooltipActionUrl;
    /**
     * @var string
     */
    protected $migrationApiRoot;

    public function __construct()
    {
        $this->apiRoot = esc_url_raw(rest_url('give-api/v2/admin/forms'));
        $this->bannerActionUrl = admin_url('admin-ajax.php?action=givewp_show_onboarding_banner');
        $this->tooltipActionUrl = admin_url('admin-ajax.php?action=givewp_show_upgraded_tooltip');
        $this->migrationApiRoot = esc_url_raw(rest_url('give-api/v2/admin/forms/migrate'));
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
            '/wp-admin/edit.php?post_type=give_forms', // Legacy donation form listing page.
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
            'apiRoot' => $this->apiRoot,
            'bannerActionUrl' => $this->bannerActionUrl,
            'tooltipActionUrl' => $this->tooltipActionUrl,
            'apiNonce' => $this->apiNonce,
            'preload' => $this->preloadDonationForms(),
            'authors' => $this->getAuthors(),
            'table' => give(DonationFormsListTable::class)->toArray(),
            'adminUrl' => $this->adminUrl,
            'pluginUrl' => GIVE_PLUGIN_URL,
            'showUpgradedTooltip' => !get_user_meta(get_current_user_id(), 'givewp-show-upgraded-tooltip', true),
            'supportedAddons' => $this->getSupportedAddons(),
            'supportedGateways' => $this->getSupportedGateways(),
            'isOptionBasedFormEditorEnabled' => OptionBasedFormEditor::isEnabled(),
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

        wp_enqueue_style('givewp-design-system-foundation');
    }

    /**
     * Load migration onboarding scripts
     * @since 3.2.0
     *
     * @return void
     */
    public function loadMigrationScripts()
    {
        if ($this->isShowingAddV2FormPage()) {
            EnqueueScript::make('give-add-v2form', 'assets/dist/js/give-add-v2form.js')
                ->loadInFooter()
                ->registerTranslations()
                ->registerLocalizeData('GiveDonationForms', [
                    'supportedAddons' => $this->getSupportedAddons(),
                    'supportedGateways' => $this->getSupportedGateways(),
                ])
                ->enqueue();

            wp_enqueue_style('givewp-design-system-foundation');
        }

        if ($this->isShowingEditV2FormPage()) {
            EnqueueScript::make('give-edit-v2form', 'assets/dist/js/give-edit-v2form.js')
                ->loadInFooter()
                ->registerTranslations()
                ->registerLocalizeData('GiveDonationForms', [
                    'supportedAddons' => $this->getSupportedAddons(),
                    'supportedGateways' => $this->getSupportedGateways(),
                    'migrationApiRoot' => $this->migrationApiRoot,
                    'apiNonce' => $this->apiNonce,
                    'isMigrated' => _give_is_form_migrated((int)$_GET['post']),
                ])
                ->enqueue();

            wp_enqueue_style('givewp-design-system-foundation');
        }
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

        $request = WP_REST_Request::from_url(
            add_query_arg(
                $queryParameters,
                $this->apiRoot
            )
        );

        return rest_do_request($request)->get_data();
    }

    /**
     * Get a list of author user IDs and names
     * @since 2.20.0
     */
    public function getAuthors()
    {
        $author_users = get_users([
            'role__in' => ['author', 'administrator'],
        ]);

        return array_map(function ($user) {
            return [
                'id' => $user->ID,
                'name' => $user->display_name,
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
     * Render the migration guide box on the old edit donation form page
     *
     * @since 3.0.0
     *
     * @param  WP_Post  $post
     *
     * @return void
     */
    public function renderMigrationGuideBox(WP_Post $post)
    {
        if ($post->post_type === 'give_forms') {
            echo '<div id="give-admin-edit-v2form"></div>';
        }
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
                fetch('<?php echo esc_url_raw(rest_url('give-api/v2/admin/forms/view?isLegacy=0')) ?>', {
                    method: 'GET',
                    headers: {
                        ['X-WP-Nonce']: '<?php echo wp_create_nonce('wp_rest') ?>'
                    }
                })
                    .then((res) => {
                        window.location = window.location.href = '/wp-admin/edit.php?post_type=give_forms&page=give-forms';
                    });
            }

            jQuery(function() {
                jQuery(jQuery('.wrap .page-title-action')[0]).after(
                    '<button class="page-title-action" onclick="showReactTable()"><?php _e(
                        'Switch to New View',
                        'give'
                    ) ?></button>'
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
     * Helper function to determine if current page is the edit v2 form page
     *
     * @since 3.2.1 added global $post to isset
     * @since 3.0.0
     *
     * @return bool
     */
    private function isShowingEditV2FormPage(): bool
    {
        return isset($_GET['action'], $GLOBALS['post']) && $_GET['action'] === 'edit' && $GLOBALS['post']->post_type === 'give_forms';
    }

    /**
     * Helper function to determine if current page is the add v2 form page
     *
     * @since 3.0.0
     *
     * @return bool
     */
    private function isShowingAddV2FormPage(): bool
    {
        return !isset($_GET['page']) && isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms';
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
    public static function getUrl(): string
    {
        return add_query_arg(['page' => 'give-forms'], admin_url('edit.php?post_type=give_forms'));
    }

    /**
     * Get an array of supported addons
     *
     * @since 3.14.0 Added support for Razorpay
     * @since 3.4.2 Added support for Gift Aid
     * @since 3.3.0 Add support to the Funds and Designations addon
     * @since 3.0.0
     * @return array
     */
    public function getSupportedAddons(): array
    {
        $supportedAddons = [
            'Recurring Donation' => class_exists('Give_Recurring'),
            'Fee Recovery' => class_exists('Give_Fee_Recovery'),
            'Currency Switcher' => class_exists('Give_Currency_Switcher'),
            'Form Field Manager' => class_exists('Give_Form_Fields_Manager'),
            'Tributes' => class_exists('Give_Tributes'),
            'Google Analytics Donation Tracking' => class_exists('Give_Google_Analytics'),
            'PDF Receipts' => class_exists('Give_PDF_Receipts'),
            'Annual Receipts' => class_exists('Give_Annual_Receipts'),
            'Webhooks' => defined('GIVE_WEBHOOKS_VERSION'),
            'Email Reports' => defined('GIVE_EMAIL_REPORTS_VERSION'),
            'Zapier' => defined('GIVE_ZAPIER_VERSION'),
            'Salesforce' => defined('GIVE_SALESFORCE_VERSION'),
            'Donation Upsells for WooCommerce' => class_exists('Give_WooCommerce'),
            'Constant Contact' => class_exists('Give_Constant_Contact'),
            'MailChimp' => class_exists('Give_MailChimp'),
            'Manual Donations' => class_exists('Give_Manual_Donations'),
            'Funds' => defined('GIVE_FUNDS_ADDON_NAME'),
            'Peer-to-Peer' => defined('GIVE_P2P_NAME'),
            'Gift Aid' => class_exists('Give_Gift_Aid'),
            'Text-to-Give' => defined('GIVE_TEXT_TO_GIVE_ADDON_NAME'),
            'Double the Donation' => defined('GIVE_DTD_NAME'),
            'Per Form Gateways' => class_exists('Give_Per_Form_Gateways'),
            'ConvertKit' => defined('GIVE_CONVERTKIT_VERSION'),
            'ActiveCampaign' => class_exists('Give_ActiveCampaign'),
            'Razorpay' => class_exists('Give_Razorpay_Gateway'),
        ];

        $output = [];

        foreach ($supportedAddons as $name => $isInstalled) {
            if ($isInstalled) {
                $output[] = $name;
            }
        }

        return $output;
    }

    /**
     * Get an array of supported gateways
     *
     * @since 3.0.0
     * @return array
     */
    public function getSupportedGateways(): array
    {
        $gateways = give_get_payment_gateways();
        $supportedGateways = array_intersect_key($gateways, give()->gateways->getPaymentGateways(3));

        ksort($supportedGateways);
        unset($supportedGateways['manual']);

        return array_map(function ($gateway) {
            return $gateway['admin_label'];
        }, $supportedGateways);
    }
}
