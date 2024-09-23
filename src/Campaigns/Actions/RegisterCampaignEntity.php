<?php

namespace Give\Campaigns\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class RegisterCampaignEntity
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $handleName = 'givewp-campaign-entitiy';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignEntity.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignEntity.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);
    }
}
