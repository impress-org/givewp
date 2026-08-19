<?php

namespace Give\API\REST\V3\Entities\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.13.1
 */
class RegisterAdminEntities
{
    /**
     * @since 4.13.1
     */
    public function __invoke()
    {
        $handleName = 'givewp-entities-admin';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/entitiesAdmin.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/entitiesAdmin.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);
    }
}
