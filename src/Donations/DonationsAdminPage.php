<?php

namespace Give\Donations;

use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\ViewModels\DonationDetailsViewModel;
use Give\Framework\Database\DB;
use Give\Helpers\EnqueueScript;
use Give\Helpers\Utils;

class DonationsAdminPage
{
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
     * @since 2.24.0 Add ListTable columns
     * @since 2.20.0
     * @since 2.21.2 Localized the admin URL as a base for URL concatenation.
     */
    public function loadScripts()
    {
        $data = [
            'apiNonce' => $this->apiNonce,
            'adminUrl' => $this->adminUrl,
            'paymentMode' => give_is_test_mode(),
            'forms' => $this->getForms(),
            'manualDonations' => Utils::isPluginActive('give-manual-donations/give-manual-donations.php'),
        ];

        /**
         * Render admin page container
         *
         * @unreleased conditionally enqueue scripts.
         */
        if (self::isDonationDetailsPage()) {
            $donationDetailsViewModel = new DonationDetailsViewModel(intval($_GET['id']));

            $data = array_merge(
                $data,
                [
                    'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/donation')),
                    'donationDetails' => $donationDetailsViewModel->exports(),
                    'countriesList' => give_get_country_list(),
                ]
            );

            EnqueueScript::make('give-admin-donation-details', 'assets/dist/js/give-admin-donation-details.js')
                ->loadInFooter()
                ->registerTranslations()
                ->registerLocalizeData('GiveDonations', $data)->enqueue();
        } else {
            $data = array_merge(
                $data,
                [
                    'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/donations')),
                    'table' => give(DonationsListTable::class)->toArray(),
                ]
            );

            EnqueueScript::make('give-admin-donations', 'assets/dist/js/give-admin-donations.js')
                ->loadInFooter()
                ->registerTranslations()
                ->registerLocalizeData('GiveDonations', $data)->enqueue();
        }

        wp_enqueue_style(
            'my-google-fonts',
            'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@400;500;600;700;800&display=swap',
            array(),
            '1.0.0'
        );

        wp_enqueue_style('givewp-design-system-foundation');

    }

    /**
     * Render admin page container
     * @unreleased render new root div to load admin detail screen.
     * @since 2.20.0
     *
     */
    public function render()
    {
        if (self::isDonationDetailsPage()) {
            echo '<div id="give-admin-donation-details-root"></div>';
        } else {
            echo '<div id="give-admin-donations-root"></div>';
        }
    }

    /**
     * Helper function to determine if current page is Give Donors admin page
     * @unreleased check for both admin pages to determine if page is showing.
     * @since      2.20.0
     *
     * @return bool
     *
     */
    public static function isShowing()
    {
        return (isset($_GET['page']) && $_GET['page'] === 'give-payment-history') || self::isDonationDetailsPage();
    }

    /**
     * Check if current page is donation details page
     *
     * @unreleased
     */
    private static function isDonationDetailsPage(): bool
    {
        return is_admin() && isset($_GET['view']) && 'view-payment-details' === $_GET['view'];
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

        if (self::isDonationDetailsPage()) {
            return $options;
        }

        return array_merge([
            [
                'value' => '0',
                'text' => 'Any',
            ],
        ], $options);
    }
}
