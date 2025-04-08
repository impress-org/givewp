<?php

namespace Give\Campaigns\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;

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
     * @since 4.0.0
     */
    public function loadBlockEditorAssets(): void
    {
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignBlocks.asset.php');

        wp_register_script(
            'givewp-campaign-blocks',
            GIVE_PLUGIN_URL . 'build/campaignBlocks.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script('givewp-campaign-blocks');
        wp_enqueue_style(
            'givewp-campaign-blocks',
            GIVE_PLUGIN_URL . 'build/campaignBlocks.css',
            ['wp-components'],
            $scriptAsset['version']
        );

        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignBlocksLandingPage.asset.php');

        wp_register_script(
            'givewp-campaign-landing-page-blocks',
            GIVE_PLUGIN_URL . 'build/campaignBlocksLandingPage.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_enqueue_script('givewp-campaign-landing-page-blocks');
        wp_enqueue_style(
            'givewp-campaign-landing-page-blocks',
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
