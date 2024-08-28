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
        LoadCampaignsListTableAssets::registerAssets();
        LoadCampaignsListTableAssets::enqueueAssets();
    }

    /**
     * @unreleased
     */
    public static function handleName(): string
    {
        return 'givewp-admin-campaigns-list-table';
    }

    /**
     * @unreleased
     */
    public static function registerAssets()
    {
        /*wp_register_style(
            LoadCampaignsListTableAssets::handleName(),
            GIVE_PLUGIN_URL . 'assets/dist/css/give-admin-campaigns-list-table.css',
            [],
            GIVE_VERSION
        );*/

        wp_register_script(
            LoadCampaignsListTableAssets::handleName(),
            GIVE_PLUGIN_URL . 'assets/dist/js/give-admin-campaigns-list-table.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script(LoadCampaignsListTableAssets::handleName(), 'GiveCampaignsListTable',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/campaigns/list-table')),
                'apiNonce' => wp_create_nonce('wp_rest'),
                'table' => give(CampaignsListTable::class)->toArray(),
                'adminUrl' => admin_url(),
                'paymentMode' => give_is_test_mode(),
                'pluginUrl' => GIVE_PLUGIN_URL,
            ]
        );
    }

    /**
     * @unreleased
     */
    public static function enqueueAssets()
    {
        wp_enqueue_style('givewp-design-system-foundation');
        //wp_enqueue_style(LoadCampaignsListTableAssets::handleName());
        wp_enqueue_script(LoadCampaignsListTableAssets::handleName());
    }
}
