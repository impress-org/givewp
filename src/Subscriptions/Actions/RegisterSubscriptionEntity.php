<?php

namespace Give\Subscriptions\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.8.0
 */
class RegisterSubscriptionEntity
{
    /**
     * @since 4.8.0
     */
    public function __invoke()
    {
        $handleName = 'givewp-subscription-entity';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/subscriptionEntity.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/subscriptionEntity.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);
    }
}
