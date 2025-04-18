<?php

namespace Give\DonationForms\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class RegisterFormEntity
{
    /**
     * @unreleased 
     */
    public function __invoke()
    {
        $handleName = 'givewp-form-entity';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/formEntity.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/formEntity.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);
    }
}
