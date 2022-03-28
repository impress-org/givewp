<?php

namespace Give\Donations;

use Give\Helpers\EnqueueScript;

class DonationsAdminPage
{
    /**
     * @unreleased
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
     * @unreleased
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/donations')),
            'apiNonce' => wp_create_nonce('wp_rest'),
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
     * @unreleased
     */
    public function render()
    {
        echo '<div id="give-admin-donations-root"></div>';
    }

    /**
     * Helper function to determine if current page is Give Donors admin page
     * @unreleased
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-payment-history';
    }
}
