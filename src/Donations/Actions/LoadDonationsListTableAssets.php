<?php

namespace Give\Donations\Actions;

use Give\Donations\ListTable\DonationsListTable;
use Give\Framework\Database\DB;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;
use Give\Helpers\Utils;

/**
 * @since 4.6.0
 */
class LoadDonationsListTableAssets
{
    /**
     * @since 2.27.1 Pass dismissed recommendations to the localize script
     * @since 2.20.0
     */
    public function __invoke()
    {
        $handleName = 'give-admin-donations';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/assets/dist/js/give-admin-donations.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/assets/dist/js/give-admin-donations.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_localize_script($handleName, 'GiveDonations', [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/donations')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'campaigns' => $this->getCampaigns(),
            'table' => give(DonationsListTable::class)->toArray(),
            'adminUrl' => admin_url(),
            'pluginUrl' => GIVE_PLUGIN_URL,
            'dismissedRecommendations' => $this->getDismissedRecommendations(),
            'addonsBulkActions' => [],
            'paymentMode' => give_is_test_mode(),
            'manualDonations' => Utils::isPluginActive('give-manual-donations/give-manual-donations.php'),
        ]);

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            'give-admin-ui-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400..700&display=swap',
            [],
            null
        );

        wp_enqueue_style('givewp-design-system-foundation');

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/assets/dist/js/give-admin-donations.css',
            [],
            $asset['version']
        );
    }

    /**
     * Retrieve a list of donation forms to populate the form filter dropdown
     *
     * @since 4.0.0 replace formselect with campaigns.
     * @since 2.20.0
     * @return array
     */
    private function getCampaigns()
    {
        $options = DB::table('give_campaigns')
            ->select(
                ['id', 'value'],
                ['campaign_title', 'text']
            )
            ->getAll(ARRAY_A);

        return array_merge(
            [
                [
                    'value' => '0',
                    'text' => __('Any', 'give'),
                ]
            ],
            $options
        );
    }

    /**
     * Retrieve a list of dismissed recommendations.
     *
     * @since 2.27.1
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
