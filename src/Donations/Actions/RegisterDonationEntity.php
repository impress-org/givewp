<?php

namespace Give\Donations\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @unreleased
 */
class RegisterDonationEntity
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $handleName = 'givewp-donation-entity';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/donationEntity.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/donationEntity.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);
    }
}
