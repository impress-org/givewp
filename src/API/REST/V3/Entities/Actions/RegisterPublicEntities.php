<?php

namespace Give\API\REST\V3\Entities\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @unreleased
 */
class RegisterPublicEntities
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $handleName = 'givewp-entities-public';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/entitiesPublic.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/entitiesPublic.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);
    }
}
