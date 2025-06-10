<?php

namespace Give\Campaigns\Actions;

use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\ListTable\CampaignsListTable;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.0.0
 */
class LoadCampaignsListTableAssets
{
    /**
     * @since 4.3.0 set script translations
     * @since 4.0.0
     */
    public function __invoke()
    {
        $handleName = 'givewp-admin-campaigns-list-table';
        $asset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignListTable.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignListTable.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_localize_script($handleName, 'GiveCampaignsListTable',
            [
                'apiRoot' => esc_url_raw(rest_url(CampaignRoute::NAMESPACE . '/campaigns/list-table')),
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

        Language::setScriptTranslations($handleName);

        wp_enqueue_style('givewp-design-system-foundation');
        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignListTable.css',
            [],
            $asset['version']
        );
    }
}
