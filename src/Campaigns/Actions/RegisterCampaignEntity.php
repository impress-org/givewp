<?php

namespace Give\Campaigns\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.0.0
 */
class RegisterCampaignEntity
{
    /**
     * @since 4.3.0 set script translations
     * @since 4.0.0
     */
    public function __invoke()
    {
        $handleName = 'givewp-campaign-entity';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignEntity.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignEntity.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);
    }
}
