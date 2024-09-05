<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\ListTable\CampaignsListTable;

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

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'assets/dist/js/give-admin-campaigns-list-table.js',
            [],
            GIVE_VERSION,
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
            ]
        );

        wp_enqueue_script($handleName);
        wp_enqueue_style('givewp-design-system-foundation');
    }
}
