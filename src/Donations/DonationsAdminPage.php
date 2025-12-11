<?php

namespace Give\Donations;

use Give\Donations\Actions\LoadDonationDetailsAssets;
use Give\Donations\Actions\LoadDonationsListTableAssets;
use Give\Donations\Models\Donation;

class DonationsAdminPage
{

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
        give(LoadDonationsListTableAssets::class)();
    }

    /**
     * Render admin page container
     *
     * @since 4.6.0 Add new details page view
     * @since 2.20.0
     */
    public function render()
    {
        if (isset($_GET['view']) && 'view-payment-details' === $_GET['view']) {
            include GIVE_PLUGIN_DIR . 'includes/admin/payments/view-payment-details.php';
        } else {
            if (self::isShowingDetailsPage()) {
                $donation = Donation::find(absint($_GET['id']));

                if ( ! $donation) {
                    wp_die(__('Donation not found', 'give'), 404);
                }

                give(LoadDonationDetailsAssets::class)();
            } else {
                // TODO: Remove this once the new view is fully launched
                if (self::isShowing()) {
                    give(LoadDonationsListTableAssets::class)();
                }
            }

            echo '<div id="give-admin-donations-root"></div>';
        }
    }

    /**
     * Helper function to determine if current page is Give Donations admin page
     * @since 2.20.0
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-payment-history' && ! isset($_GET['id']);
    }

    /**
     * @since 4.6.0
     */
    public static function isShowingDetailsPage(): bool
    {
        return isset($_GET['id'], $_GET['page']) && 'give-payment-history' === $_GET['page'];
    }


}
