<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\ListTable\CampaignsListTable;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class LoadCampaignsListTableAssets
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $handleName = 'givewp-admin-campaigns-list-table';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'assets/dist/js/give-admin-campaigns-list-table.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'assets/dist/js/give-admin-campaigns-list-table.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_localize_script($handleName, 'GiveCampaignsListTable',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/campaigns/list-table')),
                'apiNonce' => wp_create_nonce('wp_rest'),
                'table' => give(CampaignsListTable::class)->toArray(),
                'adminUrl' => admin_url(),
                'paymentMode' => give_is_test_mode(),
                'pluginUrl' => GIVE_PLUGIN_URL,
                'currency' => give_get_currency(),
                'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION') ? GIVE_RECURRING_VERSION : null,
            ]
        );

        wp_enqueue_script($handleName);
        wp_enqueue_style('givewp-design-system-foundation');
    }
}
