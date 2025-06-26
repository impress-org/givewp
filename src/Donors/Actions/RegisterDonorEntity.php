<?php

namespace Give\Donors\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.4.0
 */
class RegisterDonorEntity
{
    /**
     * @since 4.4.0
     */
    public function __invoke()
    {
        $handleName = 'givewp-donor-entity';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/donorEntity.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/donorEntity.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);
    }
}
