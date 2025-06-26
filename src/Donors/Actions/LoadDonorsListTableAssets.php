<?php

namespace Give\Donors\Actions;

use Give\Donors\ListTable\DonorsListTable;
use Give\Framework\Database\DB;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;
use Give\Helpers\Utils;

/**
 * @since 4.4.0
 */
class LoadDonorsListTableAssets
{
    /**
     * @since 2.27.1 Pass dismissed recommendations to the localize script
     * @since 2.20.0
     */
    public function __invoke()
    {
        $handleName = 'give-admin-donors';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/assets/dist/js/give-admin-donors.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/assets/dist/js/give-admin-donors.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_localize_script($handleName, 'GiveDonors', [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/admin/donors')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'forms' => $this->getForms(),
            'table' => give(DonorsListTable::class)->toArray(),
            'adminUrl' => admin_url(),
            'pluginUrl' => GIVE_PLUGIN_URL,
            'dismissedRecommendations' => $this->getDismissedRecommendations(),
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
            GIVE_PLUGIN_URL . 'build/assets/dist/js/give-admin-donors.css',
            [],
            $asset['version']
        );
    }

    /**
     * Preload initial table data
     *
     * @since 2.20.0
     */
    private function getForms(): array
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
