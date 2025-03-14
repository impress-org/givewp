<?php

namespace Give\Donations;

use Give\Donations\ListTable\DonationsListTable;
use Give\Framework\Database\DB;
use Give\Helpers\EnqueueScript;
use Give\Helpers\Utils;

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
            [$this, 'render']
        );
    }

    /**
     * @since      2.27.1 Add dismissed recommendations
     * @since      2.27.0 Adds "addonsBulkActions" to the GiveDonations object
     * @since      2.24.0 Add ListTable columns
     * @since      2.20.0
     * @since      2.21.2 Localized the admin URL as a base for URL concatenation.
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => $this->apiRoot,
            'apiNonce' => $this->apiNonce,
            'forms' => $this->getForms(),
            'table' => give(DonationsListTable::class)->toArray(),
            'adminUrl' => $this->adminUrl,
            'paymentMode' => give_is_test_mode(),
            'manualDonations' => Utils::isPluginActive('give-manual-donations/give-manual-donations.php'),
            'pluginUrl' => GIVE_PLUGIN_URL,
            'dismissedRecommendations' => $this->getDismissedRecommendations(),
            'addonsBulkActions' => [],
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

        wp_enqueue_style('givewp-design-system-foundation');
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
        return isset($_GET['page']) && $_GET['page'] === 'give-payment-history' && ! isset($_GET['view']);
    }

    /**
     * Retrieve a list of donation forms to populate the form filter dropdown
     *
     * @since 2.20.0
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
                'text' => __('Any', 'give'),
            ],
        ], $options);
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

        $recurringAddonIsActive = Utils::isPluginActive('give-recurring/give-recurring.php');
        $feeRecoveryAddonIsActive = Utils::isPluginActive('give-fee-recovery/give-fee-recovery.php');
        $designatedFundsAddonIsActive = Utils::isPluginActive('give-funds/give-funds.php');

        $optionNames = [
            'givewp_donations_recurring_recommendation_dismissed' => $recurringAddonIsActive,
            'givewp_donations_fee_recovery_recommendation_dismissed' => $feeRecoveryAddonIsActive,
            'givewp_donations_designated_funds_recommendation_dismissed' => $designatedFundsAddonIsActive,
        ];

        foreach ($optionNames as $optionName => $isActive) {
            $dismissed = get_option($optionName, false);
            if ($dismissed || $isActive) {
                $dismissedRecommendations[] = $optionName;
            }
        }

        return $dismissedRecommendations;
    }
}
