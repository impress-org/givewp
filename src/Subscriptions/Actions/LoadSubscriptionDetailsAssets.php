<?php

namespace Give\Subscriptions\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.8.0
 */
class LoadSubscriptionDetailsAssets
{
    /**
     * @since 4.8.0
     */
    public function __invoke()
    {
        $handleName = 'givewp-admin-subscription-details';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR.'build/subscriptionDetails.asset.php');

        wp_enqueue_editor();

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL.'build/subscriptionDetails.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);

        wp_enqueue_style('givewp-design-system-foundation');
        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL.'build/subscriptionDetails.css',
            /** @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-components/#usage */
            ['wp-components'],
            $scriptAsset['version']
        );
    }
}
