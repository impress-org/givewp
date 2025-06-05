<?php

namespace Give\Campaigns\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.0.0
 */
class RegisterCampaignBlocks
{
    /**
     * @since 4.0.0
     */
    public function __invoke()
    {
        $blocks = glob(dirname(__DIR__) . '/Blocks/*', GLOB_ONLYDIR);

        array_map('register_block_type', $blocks);

        $this->registerSharedStyles();
    }

    /**
     * @since 4.3.0 set script translations
     * @since 4.0.0
     */
    public function loadBlockEditorAssets(): void
    {
        $handleName = 'givewp-campaign-blocks';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignBlocks.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignBlocks.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );
        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignBlocks.css',
            ['wp-components'],
            $scriptAsset['version']
        );

        $handleName = 'givewp-campaign-landing-page-blocks';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignBlocksLandingPage.asset.php');

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignBlocksLandingPage.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );
        wp_enqueue_script($handleName);

        Language::setScriptTranslations($handleName);

        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignBlocksLandingPage.css',
            ['wp-components'],
            $scriptAsset['version']
        );
    }

    /**
     * @since 4.0.0
     */
    private function registerSharedStyles(): void
    {
        wp_enqueue_style('givewp-design-system-foundation');
        wp_enqueue_style(
            'givewp-campaign-blocks-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
        );
    }
}
